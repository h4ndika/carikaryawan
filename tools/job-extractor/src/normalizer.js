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

export function normalizeGlintsJob(item) {
  const position = item.title || 'Lowongan Kerja';
  const company = item.company_name || 'Perusahaan';
  const locObj = item.location || {};
  const city = locObj.locality || locObj.raw || '';
  const location = [city, locObj.country || 'Indonesia'].filter(Boolean).join(', ');
  
  let salary = '-';
  if (item.salary_minimum) {
    const cur = item.salary_currency || 'IDR';
    const min = Number(item.salary_minimum).toLocaleString('id-ID');
    const max = item.salary_maximum ? Number(item.salary_maximum).toLocaleString('id-ID') : '';
    salary = `${cur} ${min}${max ? ' - ' + max : ''}${item.salary_period ? ' / ' + item.salary_period : ''}`;
  }

  const applyUrl = item.platform_url || item.official_url || '';
  const postedAt = item.posted_date ? item.posted_date.split('T')[0] : new Date().toISOString().split('T')[0];
  const images = item.company_logo ? [item.company_logo] : [];

  return {
    title: `${position} - ${company}${city ? ' (' + city + ')' : ''}`,
    position,
    company,
    company_logo: item.company_logo || null,
    location,
    city,
    salary,
    job_type: item.job_type || 'Full-time',
    skills: item.skills || '',
    description: item.description || '',
    apply_url: applyUrl,
    posted_at: postedAt,
    images,
    source: 'glints',
    category: 'Lowongan Kerja',
    details: {
      posisi: position,
      perusahaan: company,
      lokasi: location,
      gaji: salary,
      tipe_pekerjaan: item.job_type || 'Full-time',
      keahlian: item.skills || '-',
      kualifikasi: (item.description || '').slice(0, 350) + '...',
      cara_melamar: applyUrl || 'Lamar langsung di portal Glints'
    }
  };
}

export function normalizeJobstreetJob(item) {
  const position = item.title || 'Lowongan Kerja';
  const company = item.company || 'Perusahaan';
  const location = item.location || '';
  
  const cityMatch = location.match(/\b(Jakarta|Bandung|Surabaya|Semarang|Yogyakarta|Jogja|Denpasar|Bali|Medan|Malang|Solo|Surakarta|Bogor|Bekasi|Tangerang|Depok|Cimahi)\b/i);
  const city = cityMatch ? cityMatch[1] : (location.split(',')[0] || '');

  const salary = item.salary || '-';
  const applyUrl = item.url || (item.id ? `https://id.jobstreet.com/job/${item.id}` : '');
  const postedAt = item.postedAt_iso ? item.postedAt_iso.split('T')[0] : (item.postedAt_display || new Date().toISOString().split('T')[0]);
  const description = item.description_text || item.teaser || '';

  return {
    title: `${position} - ${company}${city ? ' (' + city + ')' : ''}`,
    position,
    company,
    company_logo: null,
    location,
    city,
    salary,
    job_type: item.workType || 'Full-time',
    skills: item.classification || '',
    description,
    apply_url: applyUrl,
    posted_at: postedAt,
    images: [],
    source: 'jobstreet',
    category: 'Lowongan Kerja',
    details: {
      posisi: position,
      perusahaan: company,
      lokasi: location,
      gaji: salary,
      tipe_pekerjaan: item.workType || 'Full-time',
      kategori: item.classification || item.subClassification || '-',
      kualifikasi: description.slice(0, 350) + '...',
      cara_melamar: applyUrl || 'Lamar langsung di portal JobStreet'
    }
  };
}

