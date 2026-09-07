/**
 * Normalizer modul untuk menyeragamkan output data loker dari berbagai engine (Google Jobs & Instagram)
 * ke format standar JSON DiSekitar.
 */

export function normalizeGoogleJob(item) {
  const position = item.title || item.jobTitle || 'Lowongan Kerja';
  const company = item.companyName || item.company || 'Perusahaan Lokal';
  const location = item.location || item.address || '';
  const description = item.description || item.snippet || '';
  const salary = item.salary || item.salaryInfo || item.extensions?.find(e => e.includes('Rp') || e.toLowerCase().includes('bulan')) || '-';
  const applyUrl = item.applyUrl || item.jobUrl || item.link || (item.applyLinks?.[0]?.link) || '';
  const postedAt = item.postedAt || item.datePosted || new Date().toISOString().split('T')[0];

  // Ekstrak kota perkiraan dari lokasi
  const cityMatch = location.match(/\b(Jakarta|Bandung|Surabaya|Semarang|Yogyakarta|Jogja|Denpasar|Bali|Medan|Malang|Solo|Surakarta|Bogor|Bekasi|Tangerang|Depok)\b/i);
  const city = cityMatch ? cityMatch[1] : '';

  return {
    title: `${position} - ${company}${city ? ' (' + city + ')' : ''}`,
    position,
    company,
    location,
    city,
    salary,
    description,
    apply_url: applyUrl,
    posted_at: postedAt,
    source: 'google-jobs',
    category: 'Lowongan Kerja',
    details: {
      posisi: position,
      perusahaan: company,
      lokasi: location,
      gaji: salary,
      kualifikasi: description.slice(0, 300) + '...',
      cara_melamar: applyUrl || 'Cek sumber informasi lowongan'
    }
  };
}

export function normalizeInstagramPost(item) {
  const caption = item.caption || item.text || '';
  const firstLine = caption.split('\n')[0] || 'Info Lowongan Kerja';
  const shortTitle = firstLine.slice(0, 70).trim();
  const postUrl = item.url || (item.shortCode ? `https://instagram.com/p/${item.shortCode}` : '');
  const images = item.displayUrl ? [item.displayUrl] : (item.images || []);
  const timestamp = item.timestamp || new Date().toISOString();

  // Ekstrak perkiraan kota
  const cityMatch = caption.match(/\b(Jakarta|Bandung|Surabaya|Semarang|Yogyakarta|Jogja|Denpasar|Bali|Medan|Malang|Solo|Surakarta|Bogor|Bekasi|Tangerang|Depok)\b/i);
  const city = cityMatch ? cityMatch[1] : '';

  return {
    title: shortTitle || `Info Loker Lokal ${city}`,
    position: shortTitle,
    company: item.ownerUsername || 'Instagram Loker',
    location: city ? `${city}, Indonesia` : 'Indonesia',
    city,
    salary: '-',
    description: caption,
    apply_url: postUrl,
    posted_at: timestamp.split('T')[0],
    images,
    source: 'instagram',
    category: 'Lowongan Kerja',
    details: {
      posisi: shortTitle,
      perusahaan: item.ownerUsername || 'Instagram',
      lokasi: city || 'Lihat detail postingan',
      gaji: '-',
      kualifikasi: caption.slice(0, 300) + '...',
      cara_melamar: postUrl
    }
  };
}
