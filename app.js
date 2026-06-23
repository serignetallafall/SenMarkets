/* ============================================================
   SENMARKET — Application JavaScript
   ============================================================ */

'use strict';

// ── DATA ──────────────────────────────────────────────────────
const PRODUCTS = [
  { id:1,  name:"Panier Tressé Traditionnel",  category:"Artisanat",    price:15000, originalPrice:25000, discount:"-40%", rating:5, reviews:128, artisan:"Aminata D.", isNew:true,  image:"images/Artisanat/panier_tresse.jpg" },
  { id:2,  name:"Bijoux en Perles Artisanaux",  category:"Artisanat",    price:8500,                                        rating:4, reviews:89,  artisan:"Fatou S.",   isNew:true,  image:"images/Artisanat/bijoux_en_perle.jpg" },
  { id:3,  name:"Beurre de Karité Pur",          category:"Cosmétiques",  price:12000, originalPrice:16000, discount:"-25%", rating:5, reviews:203, artisan:"Mariama B.",             image:"images/Cosmetiques/beurre_de_karite.jpg" },
  { id:4,  name:"Tissu Wax Premium",             category:"Artisanat",    price:18000,                                        rating:4, reviews:67,  artisan:"Oumar T.",   isNew:true,  image:"images/Artisanat/tissu_wax.jpg"},
  { id:5,  name:"Épices Thiéboudienne",          category:"Alimentaire",  price:4500,                                         rating:5, reviews:156, artisan:"Rokhaya N.",              image:"images/Alimentaire/epices_thieboudienne.jpg"},
  { id:6,  name:"Huile d'Argan Artisanale",      category:"Cosmétiques",  price:9800,  originalPrice:13000, discount:"-25%", rating:4, reviews:94,  artisan:"Aissatou D.",             image:"images/Cosmetiques/huile_argan_artisanale.jpg"},
  { id:7,  name:"Chapeau Tressé Baobab",         category:"Artisanat",    price:7200,                                         rating:5, reviews:45,  artisan:"Ibrahima S.",             image:"images/Artisanat/chapeau_tresse.jpg" },
  { id:8,  name:"Mélange d'Épices Yassa",        category:"Alimentaire",  price:3200,                                         rating:4, reviews:78,  artisan:"Ndèye F.",                image:"images/Alimentaire/melange_epices_yassa.jpg"},
  { id:9,  name:"Savon au Lait de Karité",       category:"Cosmétiques",  price:2800,                                         rating:5, reviews:312, artisan:"Coumba M.", isNew:true,   image:"images/Cosmetiques/savon_au_lait_de_karite.jpg"},
  { id:10, name:"Bogolan Tissu Authentique",     category:"Artisanat",    price:22000,                                        rating:5, reviews:38,  artisan:"Moussa K.",               image:"images/Artisanat/bogolan_tissu.jpg"},
  { id:11, name:"Jus de Bissap Premium",         category:"Alimentaire",  price:3500,                                         rating:4, reviews:167, artisan:"Soda T.",                 image:"images/Alimentaire/Jus_bissap.jpg" },
  { id:12, name:"Collier Cauris Traditionnel",   category:"Artisanat",    price:6500,  originalPrice:9000,  discount:"-28%", rating:5, reviews:52,  artisan:"Khadija B.",              image:"images/Artisanat/collier_cauris.jpg"},
];

const ARTISANS = [
  { id:1, name:"Aminata Diallo",  specialty:"Vannerie & Tressage",     location:"Dakar",       products:24, rating:4.9, reviews:342, since:2025, initials:"AD", color:"#008751", category:"Artisanat",   description:"Spécialiste de la vannerie depuis 15 ans, Aminata crée des paniers et objets décoratifs d'une finesse remarquable." },
  { id:2, name:"Moussa Kouyaté", specialty:"Tissage Bogolan",          location:"Saint-Louis",  products:18, rating:4.8, reviews:187, since:2026, initials:"MK", color:"#CE1126", category:"Artisanat",   description:"Tisserand passionné perpétuant les traditions de bogolan de la vallée du fleuve Sénégal." },
  { id:3, name:"Fatou Sarr",      specialty:"Bijouterie Artisanale",   location:"Thiès",        products:31, rating:5.0, reviews:521, since:2025, initials:"FS", color:"#7C3AED", category:"Artisanat",   description:"Créatrice de bijoux en perles, cauris et métaux précieux. Chaque pièce est un chef-d'œuvre unique." },
  { id:4, name:"Ibrahima Ndiaye", specialty:"Épices & Condiments",     location:"Ziguinchor",   products:15, rating:4.7, reviews:203, since:2026, initials:"IN", color:"#D97706", category:"Alimentaire", description:"Producteur bio d'épices authentiques de Casamance, une région aux arômes d'exception." },
  { id:5, name:"Mariama Bâ",     specialty:"Cosmétiques Naturels",    location:"Dakar",        products:22, rating:4.9, reviews:418, since:2025, initials:"MB", color:"#DB2777", category:"Cosmétiques", description:"Formulatrice de soins naturels à base de karité, d'argan et d'huiles précieuses du terroir sénégalais." },
  { id:6, name:"Oumar Traoré",   specialty:"Maroquinerie Artisanale", location:"Touba",        products:9,  rating:4.6, reviews:124, since:2026, initials:"OT", color:"#0284C7", category:"Artisanat",   description:"Maroquinier traditionnel façonnant sacs et accessoires en cuir tanné avec des méthodes ancestrales." },
  { id:7, name:"Rokhaya Niang",  specialty:"Confitures & Conserves",  location:"Kaolack",      products:12, rating:4.8, reviews:267, since:2025, initials:"RN", color:"#059669", category:"Alimentaire", description:"Productrice de confitures et conserves artisanales à partir de fruits locaux bio cueillis à maturité." },
  { id:8, name:"Souleymane Faye",specialty:"Sculpture sur Bois",      location:"Casamance",    products:16, rating:4.7, reviews:89,  since:2026, initials:"SF", color:"#92400E", category:"Artisanat",   description:"Sculpteur hors pair, Souleymane donne vie au bois de Casamance en créant masques et statues ancestraux." },
  { id:9, name:"Ndèye Touré",    specialty:"Huiles & Soins Capillaires",location:"Louga",     products:19, rating:5.0, reviews:376, since:2025, initials:"NT", color:"#DC2626", category:"Cosmétiques", description:"Experte en soins capillaires naturels, ses huiles enrichies renforcent et subliment les cheveux afros." },
];

// ── STATE ────────────────────────────────────────────────────
let cart = [];
let wishlist = [];
let currentPage = 'home';
let boutiquePage = 1;
let boutiqueCategory = 'Tous';
let artisanCategory = 'Tous';
const ITEMS_PER_PAGE = 8;

// ── NAVIGATION ───────────────────────────────────────────────
function showPage(page) {
  document.querySelectorAll('.page-section').forEach(el => el.classList.add('d-none'));
  const el = document.getElementById('page-' + page);
  if (el) { el.classList.remove('d-none'); }
  currentPage = page;
  window.scrollTo({ top: 0, behavior: 'smooth' });

  // Update nav active state
  document.querySelectorAll('.main-nav .nav-link').forEach(link => {
    link.classList.toggle('active', link.dataset.page === page);
  });

  // Render dynamic pages
  if (page === 'boutique') renderBoutique();
  if (page === 'artisans') renderArtisans();
}

function setNav(el, page) {
  showPage(page);
  return false;
}

// ── CART ─────────────────────────────────────────────────────
function addToCart(productId, e) {
  if (e) e.stopPropagation();

  fetch('cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: (() => {
      const p = PRODUCTS.find(x => x.id === productId);
      const img = p ? encodeURIComponent(p.image) : '';
      return `action=add&product_id=${productId}&quantity=1&image=${img}`;
    })()
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) { showToast(data.message || 'Erreur.'); return; }
    // Sync state local avec réponse serveur
    cart = Object.values(data.cart);
    updateCartUIFromServer(data.count, data.total);
    showToast(`🛍️ "${data.cart[productId]?.name}" ajouté au panier !`, 'success');
  })
  .catch(() => showToast('Erreur réseau. Réessayez.'));
}

function removeFromCart(id) {
  fetch('cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=remove&product_id=${id}`
  })
  .then(r => r.json())
  .then(data => {
    cart = Object.values(data.cart || {});
    updateCartUIFromServer(data.count, data.total);
    renderCartItems();
  });
}

function updateQuantity(id, delta) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  const newQty = (item.quantity || 1) + delta;
  fetch('cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update&product_id=${id}&quantity=${newQty}`
  })
  .then(r => r.json())
  .then(data => {
    cart = Object.values(data.cart || {});
    updateCartUIFromServer(data.count, data.total);
    renderCartItems();
  });
}

function updateCartUI() {
  const count = cart.reduce((s, i) => s + i.quantity, 0);
  const badge = document.getElementById('cartCount');
  if (badge) { badge.textContent = count; badge.classList.toggle('show', count > 0); }
  const hc = document.getElementById('cartHeaderCount');
  if (hc) hc.textContent = count;
  const total = cart.reduce((s, i) => s + i.price * i.quantity, 0);
  const ct = document.getElementById('cartTotal');
  if (ct) ct.textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

function updateCartUIFromServer(count, total) {
  const badge = document.getElementById('cartCount');
  if (badge) { badge.textContent = count; badge.classList.toggle('show', count > 0); }
  const hc = document.getElementById('cartHeaderCount');
  if (hc) hc.textContent = count;
  const ct = document.getElementById('cartTotal');
  if (ct) ct.textContent = (total || 0).toLocaleString('fr-FR') + ' FCFA';
}

function syncCartFromServer() {
  fetch('cart.php?action=get')
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      cart = Object.values(data.cart || {});
      updateCartUIFromServer(data.count, data.total);
    })
    .catch(() => {});
}

function renderCartItems() {
  const container = document.getElementById('cartItems');
  const footer = document.getElementById('cartFooter');

  if (cart.length === 0) {
    container.innerHTML = `
      <div class="cart-empty text-center py-5">
        <i class="bi bi-bag fs-1 text-muted opacity-50 d-block mb-3"></i>
        <p class="fw-medium mb-1">Votre panier est vide</p>
        <p class="text-muted small mb-4">Découvrez nos produits authentiques</p>
        <button class="btn btn-dark rounded-3 px-4" onclick="closeCart();showPage('boutique')">Découvrir la boutique</button>
      </div>`;
    footer.classList.add('d-none');
    return;
  }

  footer.classList.remove('d-none');
  container.innerHTML = cart.map(item => `
    <div class="d-flex gap-3 mb-3 p-3 rounded-3" style="background:#f3f5f7">
      <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0 overflow-hidden"
           style="width:72px;height:72px;background:#e8ecef"><img src="${item.image}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover"></div>
      <div class="flex-grow-1 min-width-0">
        <p class="fw-medium mb-1 small text-truncate">${item.name}</p>
        <p class="fw-semibold mb-2 small" style="color:#008751">${item.price.toLocaleString('fr-FR')} FCFA</p>
        <div class="d-flex align-items-center gap-2">
          <div class="d-flex align-items-center gap-1 bg-white rounded-2 px-2 py-1">
            <button onclick="updateQuantity(${item.id},-1)" class="btn btn-sm p-0 d-flex align-items-center justify-content-center" style="width:22px;height:22px">
              <i class="bi bi-dash text-muted"></i>
            </button>
            <span class="small fw-medium px-1">${item.quantity}</span>
            <button onclick="updateQuantity(${item.id},1)" class="btn btn-sm p-0 d-flex align-items-center justify-content-center" style="width:22px;height:22px">
              <i class="bi bi-plus text-muted"></i>
            </button>
          </div>
          <button onclick="removeFromCart(${item.id})" class="btn btn-sm p-0 text-danger small fw-medium">Retirer</button>
        </div>
      </div>
    </div>
  `).join('');

  updateCartUI();
}

function openCart() {
  renderCartItems();
  document.getElementById('cartDrawer').classList.add('open');
  document.getElementById('cartOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  document.getElementById('cartDrawer').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function checkout() {
  if (cart.length === 0) { showToast('Votre panier est vide.'); return; }
  window.location.href = 'checkout.php';
}

// ── WISHLIST ─────────────────────────────────────────────────
function toggleWishlist(id, e) {
  if (e) e.stopPropagation();
  const idx = wishlist.indexOf(id);
  if (idx > -1) { wishlist.splice(idx, 1); }
  else { wishlist.push(id); }
  // Update all heart buttons for this product
  document.querySelectorAll(`[data-wishlist="${id}"]`).forEach(btn => {
    btn.classList.toggle('active', wishlist.includes(id));
    btn.innerHTML = wishlist.includes(id)
      ? '<i class="bi bi-heart-fill text-white"></i>'
      : '<i class="bi bi-heart" style="color:#6c7275"></i>';
  });
}

// ── PRODUCT CARD BUILDER ─────────────────────────────────────
function buildProductCard(p, size = 'col-6 col-md-4 col-lg-3') {
  const stars = '★'.repeat(p.rating) + '☆'.repeat(5 - p.rating);
  const priceHTML = p.originalPrice
    ? `<span class="product-price me-2">${p.price.toLocaleString('fr-FR')} FCFA</span>
       <span class="product-price-old small">${p.originalPrice.toLocaleString('fr-FR')} FCFA</span>`
    : `<span class="product-price">${p.price.toLocaleString('fr-FR')} FCFA</span>`;

  return `
    <div class="${size}">
      <div class="product-card" onclick="showProductDetail(${p.id})">
        <div class="product-img-wrap">
        <img
  src="${p.image}"
  alt="${p.name}"
  style="width:100%;height:100%;object-fit:cover;border-radius:12px;display:block;"
  onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
/>
          <div style="width:100%;height:100%;background:linear-gradient(135deg,#f3f5f7,#e8ecef);display:none;align-items:center;justify-content:center;border-radius:12px">
            <i class="bi bi-image text-muted" style="font-size:3rem"></i>
          </div>
          <div class="product-badges">
            ${p.isNew ? '<span class="badge-new">Nouveau</span>' : ''}
            ${p.discount ? `<span class="badge-discount">${p.discount}</span>` : ''}
          </div>
          <button class="wishlist-btn ${wishlist.includes(p.id) ? 'active' : ''}"
                  data-wishlist="${p.id}"
                  onclick="toggleWishlist(${p.id},event)">
            ${wishlist.includes(p.id)
              ? '<i class="bi bi-heart-fill text-white"></i>'
              : '<i class="bi bi-heart" style="color:#6c7275"></i>'}
          </button>
          <button class="add-to-cart-btn" onclick="addToCart(${p.id},event)">Ajouter au panier</button>
        </div>
        <div class="stars mb-1">${stars} <span class="text-muted small ms-1">(${p.reviews})</span></div>
        <div class="product-name">${p.name}</div>
        <div class="mb-1">${priceHTML}</div>
        <div class="product-artisan">par ${p.artisan}</div>
      </div>
    </div>`;
}

// ── HOME PRODUCT GRID ─────────────────────────────────────────
function renderHomeProducts() {
  const grid = document.getElementById('homeProductGrid');
  if (!grid) return;
  grid.innerHTML = PRODUCTS.slice(0, 4).map(p => buildProductCard(p)).join('');
}

// ── BOUTIQUE ─────────────────────────────────────────────────
function filterBoutique(btn, cat) {
  boutiqueCategory = cat;
  boutiquePage = 1;
  document.querySelectorAll('#boutiqueCatFilters .filter-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderBoutique();
}

function renderBoutique() {
  const search = (document.getElementById('boutiqueSearch')?.value || '').toLowerCase();
  const sort = document.getElementById('boutiqueSort')?.value || 'Plus récents';

  let filtered = PRODUCTS.filter(p => {
    const matchCat = boutiqueCategory === 'Tous' || p.category === boutiqueCategory;
    const matchSearch = p.name.toLowerCase().includes(search) || p.artisan.toLowerCase().includes(search);
    return matchCat && matchSearch;
  });

  if (sort === 'Prix croissant') filtered.sort((a, b) => a.price - b.price);
  else if (sort === 'Prix décroissant') filtered.sort((a, b) => b.price - a.price);
  else if (sort === 'Mieux notés') filtered.sort((a, b) => b.rating - a.rating);
  else filtered.sort((a, b) => b.id - a.id);

  const total = filtered.length;
  const pages = Math.ceil(total / ITEMS_PER_PAGE);
  if (boutiquePage > pages) boutiquePage = 1;

  const paginated = filtered.slice((boutiquePage - 1) * ITEMS_PER_PAGE, boutiquePage * ITEMS_PER_PAGE);

  const grid = document.getElementById('boutiqueProductGrid');
  if (paginated.length === 0) {
    grid.innerHTML = `<div class="col-12 text-center py-5 text-muted"><i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>Aucun produit trouvé.</div>`;
  } else {
    grid.innerHTML = paginated.map(p => buildProductCard(p, 'col-6 col-md-4 col-lg-3')).join('');
  }

  // Pagination
  const pag = document.getElementById('boutiquePagination');
  if (pages <= 1) { pag.innerHTML = ''; return; }
  pag.innerHTML = Array.from({ length: pages }, (_, i) => `
    <button class="page-btn ${i + 1 === boutiquePage ? 'active' : ''}" onclick="goToBoutiquePage(${i + 1})">${i + 1}</button>
  `).join('');
}

function goToBoutiquePage(page) {
  boutiquePage = page;
  renderBoutique();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── ARTISANS ─────────────────────────────────────────────────
function filterArtisans(btn, cat) {
  artisanCategory = cat;
  document.querySelectorAll('#artisanCatFilters .filter-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderArtisans();
}

function renderArtisans() {
  const search = (document.getElementById('artisanSearch')?.value || '').toLowerCase();

  const filtered = ARTISANS.filter(a => {
    const matchCat = artisanCategory === 'Tous' || a.category === artisanCategory;
    const matchSearch =
      a.name.toLowerCase().includes(search) ||
      a.specialty.toLowerCase().includes(search) ||
      a.location.toLowerCase().includes(search);
    return matchCat && matchSearch;
  });

  const grid = document.getElementById('artisansGrid');
  if (filtered.length === 0) {
    grid.innerHTML = `<div class="col-12 text-center py-5 text-muted"><i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>Aucun artisan trouvé.</div>`;
    return;
  }

  grid.innerHTML = filtered.map(a => `
    <div class="col-md-6 col-lg-4">
      <div class="artisan-card h-100">
        <div class="d-flex gap-3 mb-3">
          <div class="artisan-avatar" style="background:${a.color}">${a.initials}</div>
          <div>
            <h5 class="fw-semibold mb-0">${a.name}</h5>
            <p class="text-muted small mb-1">${a.specialty}</p>
            <span class="badge text-dark" style="background:#f3f5f7;font-size:.75rem"><i class="bi bi-geo-alt text-muted me-1"></i>${a.location}</span>
          </div>
        </div>
        <p class="text-muted small lh-lg mb-3">${a.description}</p>
        <div class="d-flex justify-content-between align-items-center border-top pt-3">
          <div class="text-center">
            <p class="fw-bold mb-0">${a.products}</p>
            <p class="text-muted" style="font-size:.72rem">Produits</p>
          </div>
          <div class="text-center">
            <p class="fw-bold mb-0">⭐ ${a.rating}</p>
            <p class="text-muted" style="font-size:.72rem">${a.reviews} avis</p>
          </div>
          <div class="text-center">
            <p class="fw-bold mb-0">Depuis ${a.since}</p>
            <p class="text-muted" style="font-size:.72rem">Sur la plateforme</p>
          </div>
          <button class="btn btn-sm btn-outline-dark rounded-3 fw-medium" onclick="showPage('boutique')">
            Voir ses produits <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>
      </div>
    </div>
  `).join('');
}

// ── PRODUCT DETAIL MODAL ─────────────────────────────────────
function showProductDetail(id) {
  const p = PRODUCTS.find(x => x.id === id);
  if (!p) return;

  const stars = '★'.repeat(p.rating) + '☆'.repeat(5 - p.rating);
  const priceHTML = p.originalPrice
    ? `<span class="fw-bold fs-3">${p.price.toLocaleString('fr-FR')} FCFA</span>
       <span class="text-muted text-decoration-line-through ms-2">${p.originalPrice.toLocaleString('fr-FR')} FCFA</span>`
    : `<span class="fw-bold fs-3">${p.price.toLocaleString('fr-FR')} FCFA</span>`;

  let modal = document.getElementById('productModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'productModal';
    modal.className = 'modal fade';
    modal.setAttribute('tabindex', '-1');
    document.body.appendChild(modal);
  }

  modal.innerHTML = `
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-body p-0">
          <div class="row g-0">
            <div class="col-md-5">
              <div class="rounded-start overflow-hidden" style="height:360px">
                <img src="${p.image}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover">
              </div>
            </div>
            <div class="col-md-7 p-4 p-md-5">
              <button class="btn-close position-relati top-3 end-3" data-bs-dismiss="modal"></button>
              <div class="mb-2">
                ${p.isNew ? '<span class="badge-new me-2">Nouveau</span>' : ''}
                ${p.discount ? `<span class="badge-discount">${p.discount}</span>` : ''}
              </div>
              <h4 class="fw-semibold mb-1">${p.name}</h4>
              <div class="stars mb-2">${stars} <span class="text-muted small">(${p.reviews} avis)</span></div>
              <p class="text-muted small mb-3">par <strong>${p.artisan}</strong> · ${p.category}</p>
              <div class="mb-4">${priceHTML}</div>
              <p class="text-muted small lh-lg mb-4">
                Produit artisanal authentique fabriqué selon les traditions sénégalaises. Qualité vérifiée et garantie par SenMarket. Livraison disponible au Sénégal et à l'international.
              </p>
              <div class="d-flex gap-2 mb-3">
                <button class="btn btn-dark flex-grow-1 py-3 rounded-3 fw-semibold" onclick="addToCart(${p.id});bootstrap.Modal.getInstance(document.getElementById('productModal')).hide()">
                  <i class="bi bi-bag me-2"></i>Ajouter au panier
                </button>
                <button class="btn btn-outline-dark py-3 px-3 rounded-3" onclick="toggleWishlist(${p.id},event)">
                  <i class="bi bi-heart${wishlist.includes(p.id) ? '-fill text-danger' : ''}"></i>
                </button>
              </div>
              <div class="d-flex gap-3 text-muted small">
                <span><i class="bi bi-truck me-1 text-sengreen"></i>Livraison 48h</span>
                <span><i class="bi bi-shield-check me-1 text-sengreen"></i>Qualité garantie</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>`;

  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

// ── LOGIN ─────────────────────────────────────────────────────
// ══════════════════════════════════════════════════
// AUTHENTIFICATION
// ══════════════════════════════════════════════════

let currentUser = null;

function openLogin() {
  switchAuthTab(currentUser ? 'user' : 'login');
  clearAuthAlert();
  new bootstrap.Modal(document.getElementById('loginModal')).show();
}

function switchAuthTab(tab) {
  ['Login','Register','Forgot','User'].forEach(t => {
    const el = document.getElementById('form' + t);
    if (el) el.classList.toggle('d-none', t.toLowerCase() !== tab);
  });
  const btnL = document.getElementById('tabLoginBtn');
  const btnR = document.getElementById('tabRegisterBtn');
  const hide = (tab === 'user' || tab === 'forgot');
  if (btnL) btnL.classList.toggle('d-none', hide);
  if (btnR) btnR.classList.toggle('d-none', hide);
  if (!hide && btnL && btnR) {
    btnL.className = `btn flex-fill fw-semibold rounded-3 py-2 ${tab==='login'    ? 'btn-dark'                  : 'btn-outline-secondary'}`;
    btnR.className = `btn flex-fill fw-semibold rounded-3 py-2 ${tab==='register' ? 'btn-sengreen text-white'    : 'btn-outline-secondary'}`;
  }
  clearAuthAlert();
}

function showAuthAlert(msg, type = 'danger') {
  const el = document.getElementById('authAlert');
  if (!el) return;
  el.className = `rounded-3 p-2 small text-center alert alert-${type}`;
  el.textContent = msg;
}
function clearAuthAlert() {
  const el = document.getElementById('authAlert');
  if (el) { el.className = 'd-none'; el.textContent = ''; }
}
function setLoading(id, state) {
  document.getElementById(id)?.classList.toggle('d-none', !state);
}
function togglePwd(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon  = btn.querySelector('i');
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
  if (icon) icon.className = input.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}
function checkPwdStrength(val) {
  const bar = document.getElementById('pwdBar');
  const lbl = document.getElementById('pwdLabel');
  const wrap = document.getElementById('pwdStrength');
  if (!bar) return;
  if (!val) { wrap?.classList.add('d-none'); return; }
  wrap?.classList.remove('d-none');
  let s = 0;
  if (val.length >= 6) s++;
  if (val.length >= 10) s++;
  if (/[A-Z]/.test(val)) s++;
  if (/[0-9]/.test(val)) s++;
  if (/[^A-Za-z0-9]/.test(val)) s++;
  const L = [
    {pct:'20%',cls:'bg-danger', txt:'Très faible'},
    {pct:'40%',cls:'bg-warning',txt:'Faible'},
    {pct:'60%',cls:'bg-info',   txt:'Moyen'},
    {pct:'80%',cls:'bg-primary',txt:'Fort'},
    {pct:'100%',cls:'bg-success',txt:'Très fort'},
  ][Math.min(s,4)];
  bar.style.width = L.pct;
  bar.className = `progress-bar ${L.cls}`;
  if (lbl) lbl.textContent = L.txt;
}

function applyUser(user) {
  currentUser = user;
  const initials = (user.name||'?').split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);

  // Header : masquer icône, afficher initiales dans bulle verte
  const headerIcon     = document.getElementById('headerAuthIcon');
  const headerInitials = document.getElementById('headerAuthInitials');
  if (headerIcon)     { headerIcon.classList.add('d-none'); }
  if (headerInitials) { headerInitials.textContent = initials; headerInitials.classList.remove('d-none'); headerInitials.style.display='flex'; }

  // Modal vue connecté
  const av = document.getElementById('userAvatar');
  const gr = document.getElementById('userGreeting');
  const em = document.getElementById('userEmailDisplay');
  if (av) av.textContent = initials;
  if (gr) gr.textContent = `Bonjour, ${user.name} 👋`;
  if (em) em.textContent = user.email;
  switchAuthTab('user');
}
function clearUser() {
  currentUser = null;
  // Header : restaurer icône
  const headerIcon     = document.getElementById('headerAuthIcon');
  const headerInitials = document.getElementById('headerAuthInitials');
  if (headerIcon)     { headerIcon.classList.remove('d-none'); }
  if (headerInitials) { headerInitials.classList.add('d-none'); headerInitials.style.display='none'; }
  switchAuthTab('login');
}

async function submitLogin() {
  const email    = document.getElementById('loginEmail')?.value.trim();
  const password = document.getElementById('loginPassword')?.value;
  if (!email || !password) return showAuthAlert('Veuillez remplir tous les champs.');
  setLoading('loginSpinner', true); clearAuthAlert();
  try {
    const r = await fetch('auth.php', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    });
    const d = await r.json();
    if (d.success) {
      applyUser(d.user);
      showToast(`Bienvenue, ${d.user.name} !`, 'success');
      setTimeout(()=>bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide(), 700);
    } else { showAuthAlert(d.message || 'Email ou mot de passe incorrect.'); }
  } catch (e) {
    // Try to get more info about the error
    try {
      const r2 = await fetch('auth.php?action=me');
      const txt = await r2.text();
      if (txt.startsWith('{')) {
        showAuthAlert('auth.php répond mais login a échoué. Vérifiez la console (F12).');
      } else {
        showAuthAlert('Erreur PHP : ' + txt.substring(0, 120));
      }
    } catch {
      showAuthAlert('auth.php inaccessible. Vérifiez que le fichier existe dans htdocs/SenMarkets/');
    }
  }
  setLoading('loginSpinner', false);
}

async function submitRegister() {
  const name     = document.getElementById('regName')?.value.trim();
  const email    = document.getElementById('regEmail')?.value.trim();
  const phone    = document.getElementById('regPhone')?.value.trim();
  const password = document.getElementById('regPassword')?.value;
  const confirm  = document.getElementById('regConfirm')?.value;
  const terms    = document.getElementById('acceptTerms')?.checked;
  if (!name || !email || !password || !confirm) return showAuthAlert('Veuillez remplir tous les champs obligatoires.');
  if (password !== confirm) return showAuthAlert('Les mots de passe ne correspondent pas.');
  if (password.length < 6) return showAuthAlert('Le mot de passe doit contenir au moins 6 caractères.');
  if (!terms) return showAuthAlert("Veuillez accepter les conditions d'utilisation.");
  setLoading('regSpinner', true); clearAuthAlert();
  try {
    const r = await fetch('auth.php', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`action=register&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone||'')}&password=${encodeURIComponent(password)}&confirm=${encodeURIComponent(confirm)}`
    });
    const d = await r.json();
    if (d.success) {
      applyUser(d.user);
      showToast(`Compte créé ! Bienvenue, ${d.user.name} !`, 'success');
      setTimeout(()=>bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide(), 700);
    } else { showAuthAlert(d.message || 'Erreur lors de la création du compte.'); }
  } catch (e) {
    try {
      const r2 = await fetch('auth.php?action=me');
      const txt = await r2.text();
      showAuthAlert('Erreur PHP: ' + txt.substring(0, 120));
    } catch {
      showAuthAlert('auth.php inaccessible. Vérifiez que le fichier existe dans htdocs/SenMarkets/');
    }
  }
  setLoading('regSpinner', false);
}

async function submitLogout() {
  await fetch('auth.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=logout'}).catch(()=>{});
  clearUser();
  bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide();
  showToast('Vous êtes déconnecté.', 'success');
}

async function submitForgot() {
  const email = document.getElementById('forgotEmail')?.value.trim();
  if (!email) return showAuthAlert('Veuillez entrer votre adresse email.');
  setLoading('forgotSpinner', true); clearAuthAlert();
  try {
    const r = await fetch('auth.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=forgot&email=${encodeURIComponent(email)}`});
    const d = await r.json();
    showAuthAlert(d.message || 'Email envoyé si le compte existe.', 'success');
  } catch { showAuthAlert('Erreur réseau.'); }
  setLoading('forgotSpinner', false);
}

async function checkAuthSession() {
  try {
    const r = await fetch('auth.php?action=me');
    const d = await r.json();
    if (d.loggedIn && d.user) applyUser(d.user);
  } catch {}
}

// ── NEWSLETTER ────────────────────────────────────────────────
function handleNewsletter(e) {
  e.preventDefault();
  const input = e.target.querySelector('input[type="email"]');
  showToast(`✉️ Merci ! Vous êtes inscrit avec ${input.value}`, 'success');
  input.value = '';
}

// ── CONTACT ───────────────────────────────────────────────────
function handleContact(e) {
  e.preventDefault();
  const success = document.getElementById('contactSuccess');
  success.classList.remove('d-none');
  e.target.reset();
  setTimeout(() => success.classList.add('d-none'), 5000);
}

// ── TOAST ─────────────────────────────────────────────────────
function showToast(msg, type = '') {
  const toast = document.getElementById('mainToast');
  const toastMsg = document.getElementById('toastMsg');
  toastMsg.textContent = msg;
  toast.className = `toast align-items-center text-white border-0 ${type === 'success' ? 'bg-success' : 'bg-dark'}`;
  new bootstrap.Toast(toast, { delay: 3000 }).show();
}

// ── HERO SLIDER ───────────────────────────────────────────────
const HERO_SLIDES = [
  {
    title: `<span class="text-sengreen">L'Artisanat</span><br><span class="text-yellow">Sénégalais</span><br><span class="text-danger">Authentique</span>`,
    sub:   'Découvrez des créations uniques façonnées par des artisans talentueux du Sénégal.'
  },
  {
    title: `<span class="text-yellow">Cosmétiques</span><br><span class="text-white">Naturels</span><br><span class="text-sengreen">du Sénégal</span>`,
    sub:   'Karité, argan, savons artisanaux — les secrets de beauté de nos grand-mères.'
  },
  {
    title: `<span class="text-white">Fier</span><br><span class="text-yellow">d'être</span><br><span class="text-sengreen">Sénégalais</span>`,
    sub:   'SenMarket célèbre le savoir-faire et la culture du Sénégal à travers chaque produit.'
  },
];

let heroIdx      = 0;
let heroInterval = null;

function heroGo(dir, target) {
  const slides = document.querySelectorAll('.hero-slide');
  const dots   = document.querySelectorAll('.hero-dot');

  // Retirer l'état actif de l'ancienne slide
  slides[heroIdx]?.classList.remove('active');
  dots[heroIdx]?.classList.remove('hero-dot--active');

  // Calculer l'index cible
  if (target !== undefined) {
    heroIdx = target;
  } else {
    heroIdx = (heroIdx + dir + HERO_SLIDES.length) % HERO_SLIDES.length;
  }

  // Activer la nouvelle slide
  slides[heroIdx]?.classList.add('active');
  dots[heroIdx]?.classList.add('hero-dot--active');

  // Mettre à jour le texte
  const titleEl = document.getElementById('heroTitle');
  const subEl   = document.getElementById('heroSub');
  if (titleEl) titleEl.innerHTML = HERO_SLIDES[heroIdx].title;
  if (subEl)   subEl.textContent = HERO_SLIDES[heroIdx].sub;
}

function startHeroAuto() {
  if (heroInterval) clearInterval(heroInterval);
  heroInterval = setInterval(() => heroGo(1), 5000);
}


// ── INIT ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderHomeProducts();
  syncCartFromServer();
  checkAuthSession();
  startHeroAuto();
});