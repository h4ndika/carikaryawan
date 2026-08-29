import express from 'express';
import db from '../db.js';
import { requireAuth } from '../middleware/auth.js';
import { scrapePlacePhotos } from '../photoScraper.js';

const router = express.Router();

// Posts list page
router.get('/', requireAuth, async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = 20;
    const offset = (page - 1) * limit;

    // Get total count
    const [countResult] = await db.query('SELECT COUNT(*) as total FROM posts');
    const total = countResult[0].total;
    const totalPages = Math.ceil(total / limit);

    // Get posts with relations
    const [posts] = await db.query(`
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
      ORDER BY p.created_at DESC
      LIMIT ? OFFSET ?
    `, [limit, offset]);

    res.render('posts/index', {
      posts,
      currentPage: page,
      totalPages,
      total,
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

    // Update post thumbnail if empty
    if (photos.length > 0) {
      await db.query(
        'UPDATE posts SET thumbnail = ?, updated_at = NOW() WHERE id = ? AND (thumbnail IS NULL OR thumbnail = "")',
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
