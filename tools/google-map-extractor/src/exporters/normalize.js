// Normalizes raw items from compass/crawler-google-places into standardized business objects
export function normalizeItem(raw) {
  if (!raw || typeof raw !== 'object') return null;

  // title/name
  const title = raw.title || raw.name || '';

  // category
  const category = raw.categoryName || (Array.isArray(raw.categories) && raw.categories[0]) || '';

  // address
  const address = raw.address || formatAddress(raw);

  // phone
  const phone = raw.phone || raw.phoneUnformatted || '';

  // website
  const website = raw.website || raw.url || '';

  // rating
  const rating = raw.totalScore || raw.rating || '';

  // reviewsCount
  const reviewsCount = raw.reviewsCount ?? '';

  // price
  const price = raw.price || raw.priceLevel || '';

  // latitude / longitude
  const latitude = raw.location?.lat || raw.latitude || '';
  const longitude = raw.location?.lng || raw.longitude || '';

  // openingHours - formatted string from openingHours or array
  const openingHours = formatOpeningHours(raw.openingHours, raw.openingHoursArray || raw.openingHours);

  // description
  const description = raw.description || raw.about || '';

  // facilities / amenities
  const facilities = formatFacilities(raw.facilities || raw.amenities);

  // googleMapsUrl
  const googleMapsUrl = raw.url || raw.searchPageUrl || '';

  // imageUrls
  const imageUrls = formatImageUrls(raw.imageUrls, raw.imageUrl);

  // placeId
  const placeId = raw.placeId || '';

  // cid
  const cid = raw.cid || '';

  return {
    title,
    category,
    address,
    phone,
    website,
    rating,
    reviewsCount,
    price,
    latitude,
    longitude,
    openingHours,
    description,
    facilities,
    googleMapsUrl,
    imageUrls,
    placeId,
    cid,
  };
}

export function normalizeItems(items) {
  if (!Array.isArray(items)) return [];
  return items.map(normalizeItem).filter((item) => item !== null);
}

function formatAddress(raw) {
  if (!raw) return '';
  if (typeof raw === 'string') return raw;
  // Try formatting address parts if available
  if (typeof raw === 'object') {
    const parts = [
      raw.street,
      raw.streetNumber,
      raw.city,
      raw.state,
      raw.zip,
      raw.country,
    ].filter(Boolean);
    return parts.join(', ');
  }
  return '';
}

function formatOpeningHours(openingHours, openingHoursArray) {
  if (!openingHours && !openingHoursArray) return '';
  if (typeof openingHours === 'string') return openingHours;

  if (Array.isArray(openingHours)) {
    return openingHours.map(formatHoursEntry).filter(Boolean).join('; ');
  }

  if (openingHoursArray && Array.isArray(openingHoursArray)) {
    return openingHoursArray.map(formatHoursEntry).filter(Boolean).join('; ');
  }

  return '';
}

function formatHoursEntry(hour) {
  if (!hour) return '';
  if (typeof hour === 'string') return hour;
  if (typeof hour === 'object') {
    const day = hour.day || hour.dayOfWeek || '';
    const open = hour.open || hour.openTime || '';
    const close = hour.close || hour.closeTime || '';
    if (day && open && close) return `${day}: ${open}-${close}`;
    if (day && open) return `${day}: ${open}`;
    return day || '';
  }
  return '';
}

function formatFacilities(facilities) {
  if (!facilities) return '';
  if (typeof facilities === 'string') return facilities;
  if (Array.isArray(facilities)) {
    return facilities.map((f) => (typeof f === 'string' ? f : f?.name || '')).filter(Boolean).join(', ');
  }
  return '';
}

function formatImageUrls(imageUrls, imageUrl) {
  const list = [];
  if (Array.isArray(imageUrls)) {
    imageUrls.forEach((url) => {
      if (typeof url === 'string') list.push(url);
      else if (url && typeof url === 'object') {
        if (url.url) list.push(url.url);
        else if (url.img) list.push(url.img);
      }
    });
  }
  if (typeof imageUrl === 'string') list.push(imageUrl);
  if (list.length === 0) return [];
  return [...new Set(list)];
}
