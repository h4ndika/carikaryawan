import express from 'express';
import db from '../db.js';
import { requireAuth } from '../middleware/auth.js';
import { scrapePlacePhotos, scrapeMultiplePlacesPhotos } from '../photoScraper.js';

const router = express.Router();

// Posts list page
router.get('/', requireAuth, async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = 20;
    const offset = (page - 1) * limit;

    // Get filter parameters
    const filters = {
      status: req.query.status || '',
      category_id: req.query.category_id || '',
      city_id: req.query.city_id || '',
      province_id: req.query.province_id || '',
      photo_status: req.query.photo_status || '',
      date_from: req.query.date_from || '',
      date_to: req.query.date_to || '',
    };

    // Build WHERE clause
    const whereClauses = [];
    const queryParams = [];

    if (filters.status) {
      whereClauses.push('p.status = ?');
      queryParams.push(filters.status);
    }

    if (filters.category_id) {
      whereClauses.push('p.category_id = ?');
      queryParams.push(filters.category_id);
    }

    if (filters.city_id) {
      whereClauses.push('p.city_id = ?');
      queryParams.push(filters.city_id);
    }

    if (filters.province_id) {
      whereClauses.push('p.province_id = ?');
      queryParams.push(filters.province_id);
    }

    if (filters.date_from) {
      whereClauses.push('DATE(p.created_at) >= ?');
      queryParams.push(filters.date_from);
    }

    if (filters.date_to) {
      whereClauses.push('DATE(p.created_at) <= ?');
      queryParams.push(filters.date_to);
    }

    const whereSQL = whereClauses.length > 0 ? 'WHERE ' + whereClauses.join(' AND ') : '';

    // Get total count with filters
    const countQuery = `SELECT COUNT(*) as total FROM posts p ${whereSQL}`;
    const [countResult] = await db.query(countQuery, queryParams);
    const total = countResult[0].total;
    const totalPages = Math.ceil(total / limit);

    // Get posts with relations and filters
    const postsQuery = `
      SELECT 
        p.id,
        p.title,
        p.thumbnail,
        p.place_id,
        p.status,
        p.created_at,
        c.name as category_name,
        ci.name as city_name,
        pr.name as province_name,
        (SELECT COUNT(*) FROM post_images WHERE post_id = p.id) as images_count
      FROM posts p
      LEFT JOIN categories c ON p.category_id = c.id
      LEFT JOIN cities ci ON p.city_id = ci.id
      LEFT JOIN provinces pr ON p.province_id = pr.id
      ${whereSQL}
      ORDER BY p.created_at DESC
      LIMIT ? OFFSET ?
    `;
    
    const [posts] = await db.query(postsQuery, [...queryParams, limit, offset]);

    // Apply photo status filter (after fetch because it's computed)
    let filteredPosts = posts;
    if (filters.photo_status === '<=1') {
      filteredPosts = posts.filter(p => p.images_count <= 1);
    } else if (filters.photo_status === '>1') {
      filteredPosts = posts.filter(p => p.images_count > 1);
    }

    // Get filter options
    const [categories] = await db.query('SELECT id, name FROM categories ORDER BY name ASC');
    const [provinces] = await db.query('SELECT id, name FROM provinces ORDER BY name ASC');
    const [cities] = await db.query('SELECT id, name FROM cities ORDER BY name ASC');

    res.render('posts/index', {
      posts: filteredPosts,
      currentPage: page,
      totalPages,
      total: filteredPosts.length,
      filters,
      categories,
      provinces,
      cities,
    });
  } catch (error) {
    console.error('Posts list error:', error);
    res.status(500).send('Error loading posts');
  }
});

// Scrape photos for a post
router.post('/posts/:id/scrape-photos', requireAuth, async (req, res) => {
  const postId = req.params.id;

  try {
    // Get post details
    const [posts] = await db.query(`
      SELECT 
        p.id,
        p.title,
        p.place_id,
        c.name as category_name,
        ci.name as city_name,
        pr.name as province_name
      FROM posts p
      LEFT JOIN categories c ON p.category_id = c.id
      LEFT JOIN cities ci ON p.city_id = ci.id
      LEFT JOIN provinces pr ON p.province_id = pr.id
      WHERE p.id = ?
    `, [postId]);

    if (posts.length === 0) {
      return res.status(404).json({ success: false, message: 'Post tidak ditemukan' });
    }

    const post = posts[0];

    // Build query
    const queryParts = [];
    if (post.category_name) {
      queryParts.push(post.category_name);
    }
    queryParts.push(post.title);
    if (post.city_name) {
      const cleanCity = post.city_name.replace(/^(KOTA|KAB\.|KABUPATEN)\s+/i, '');
      queryParts.push(cleanCity);
    }
    if (post.province_name && post.province_name !== post.city_name) {
      const cleanProv = post.province_name.replace(/^(PROV\.|PROVINSI)\s+/i, '');
      queryParts.push(cleanProv);
    }
    queryParts.push('Indonesia');

    const query = queryParts.join(' ');

    // Scrape photos
    const photos = await scrapePlacePhotos(query, 12, {
      placeId: post.place_id,
      apifyToken: process.env.APIFY_TOKEN,
    });

    if (photos.length === 0) {
      return res.json({
        success: false,
        message: 'Tidak ditemukan foto untuk tempat ini',
        count: 0,
      });
    }

    // Delete existing photos
    await db.query('DELETE FROM post_images WHERE post_id = ?', [postId]);

    // Insert new photos
    let insertedCount = 0;
    for (let i = 0; i < photos.length; i++) {
      await db.query(
        'INSERT INTO post_images (post_id, image_url, caption, `order`, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
        [postId, photos[i], post.title, i]
      );
      insertedCount++;
    }

    // Update post thumbnail with the first scraped photo
    if (photos.length > 0) {
      await db.query(
        'UPDATE posts SET thumbnail = ?, updated_at = NOW() WHERE id = ?',
        [photos[0], postId]
      );
    }

    res.json({
      success: true,
      message: `Berhasil menambahkan ${insertedCount} foto`,
      count: insertedCount,
      photos: photos,
    });
  } catch (error) {
    console.error('Scrape error:', error);
    res.status(500).json({
      success: false,
      message: 'Terjadi kesalahan saat scraping foto: ' + error.message,
    });
  }
});

// Batch Scrape Photos
router.post('/posts/batch-scrape', requireAuth, async (req, res) => {
  const { postIds } = req.body;

  if (!Array.isArray(postIds) || postIds.length === 0) {
    return res.status(400).json({ success: false, message: 'Pilih setidaknya satu post.' });
  }

  try {
    const placeholders = postIds.map(() => '?').join(',');
    const [posts] = await db.query(
      `SELECT p.id, p.title, p.place_id, c.name as city_name, pr.name as province_name 
       FROM posts p 
       LEFT JOIN cities c ON p.city_id = c.id 
       LEFT JOIN provinces pr ON p.province_id = pr.id 
       WHERE p.id IN (${placeholders})`,
      postIds
    );

    if (posts.length === 0) {
      return res.status(404).json({ success: false, message: 'Tidak ada post yang ditemukan.' });
    }

    const placeIdToPosts = {};
    const fallbackPosts = [];

    for (const post of posts) {
      if (post.place_id && typeof post.place_id === 'string' && post.place_id.startsWith('ChI')) {
        if (!placeIdToPosts[post.place_id]) placeIdToPosts[post.place_id] = [];
        placeIdToPosts[post.place_id].push(post);
      } else {
        fallbackPosts.push(post);
      }
    }

    const uniquePlaceIds = Object.keys(placeIdToPosts);
    let scrapedMap = {};

    if (uniquePlaceIds.length > 0) {
      scrapedMap = await scrapeMultiplePlacesPhotos(uniquePlaceIds, 12, {
        apifyToken: process.env.APIFY_TOKEN,
      });
    }

    let successCount = 0;
    let failedCount = 0;
    const details = [];

    // Process matched placeIds
    for (const placeId of uniquePlaceIds) {
      const photos = scrapedMap[placeId] || [];
      const matchedPosts = placeIdToPosts[placeId] || [];

      for (const post of matchedPosts) {
        if (photos.length > 0) {
          // Clear and save photos
          await db.query('DELETE FROM post_images WHERE post_id = ?', [post.id]);
          for (let i = 0; i < photos.length; i++) {
            await db.query(
              'INSERT INTO post_images (post_id, image_url, caption, `order`, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
              [post.id, photos[i], post.title, i]
            );
          }
          await db.query('UPDATE posts SET thumbnail = ?, updated_at = NOW() WHERE id = ?', [
            photos[0],
            post.id,
          ]);
          successCount++;
          details.push({ postId: post.id, title: post.title, success: true, count: photos.length });
        } else {
          failedCount++;
          details.push({ postId: post.id, title: post.title, success: false, message: 'Tidak ada foto' });
        }
      }
    }

    // Process fallback posts one-by-one if any
    for (const post of fallbackPosts) {
      failedCount++;
      details.push({ postId: post.id, title: post.title, success: false, message: 'Place ID tidak valid' });
    }

    res.json({
      success: successCount > 0,
      total: posts.length,
      successCount,
      failedCount,
      message: `Selesai memproses ${posts.length} post: ${successCount} berhasil, ${failedCount} gagal.`,
      details,
    });
  } catch (error) {
    console.error('Batch scrape error:', error);
    res.status(500).json({
      success: false,
      message: 'Terjadi kesalahan saat batch scrape: ' + error.message,
    });
  }
});

// View post photos
router.get('/posts/:id/photos', requireAuth, async (req, res) => {
  const postId = req.params.id;

  try {
    const [posts] = await db.query('SELECT * FROM posts WHERE id = ?', [postId]);
    if (posts.length === 0) {
      return res.status(404).send('Post tidak ditemukan');
    }

    const [photos] = await db.query(
      'SELECT * FROM post_images WHERE post_id = ? ORDER BY `order` ASC',
      [postId]
    );

    res.render('posts/photos', {
      post: posts[0],
      photos,
    });
  } catch (error) {
    console.error('View photos error:', error);
    res.status(500).send('Error loading photos');
  }
});

export default router;
