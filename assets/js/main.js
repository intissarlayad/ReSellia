'use strict';

// ── Navbar scroll ─────────────────────────────────────────────
const navbar = document.getElementById('navbar');
if (navbar) {
  const onScroll = () => navbar.classList.toggle('scrolled', scrollY > 20);
  addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

// ── Mobile nav ────────────────────────────────────────────────
const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobile-nav');
hamburger?.addEventListener('click', () => {
  const open = hamburger.classList.toggle('open');
  mobileNav?.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
});
mobileNav?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  hamburger?.classList.remove('open');
  mobileNav.classList.remove('open');
  document.body.style.overflow = '';
}));

// ── Scroll reveal ─────────────────────────────────────────────
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));

// ── Qty controls ─────────────────────────────────────────────
document.querySelectorAll('.qty-control').forEach(ctrl => {
  const inp = ctrl.querySelector('.qty-input');
  ctrl.querySelector('.qty-minus')?.addEventListener('click', () => { const v = +inp.value; if (v > 1) inp.value = v - 1; inp.dispatchEvent(new Event('change')); });
  ctrl.querySelector('.qty-plus')?.addEventListener('click',  () => { inp.value = +inp.value + 1; inp.dispatchEvent(new Event('change')); });
});

// ── Wishlist toggle AJAX ──────────────────────────────────────
document.querySelectorAll('[data-wish]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.wish;
    try {
      const res  = await fetch('/api/wishlist-toggle.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ product_id: id }) });
      const data = await res.json();
      if (data.wishlisted !== undefined) btn.classList.toggle('active', data.wishlisted);
    } catch(e) { console.error(e); }
  });
});

// ── Add to cart AJAX ─────────────────────────────────────────
document.querySelectorAll('[data-add-cart]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id  = btn.dataset.addCart;
    const qty = document.querySelector('.qty-input')?.value || 1;
    btn.disabled = true;
    const orig = btn.textContent;
    try {
      const res  = await fetch('/api/cart-add.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ product_id: id, qty }) });
      const data = await res.json();
      if (data.success) {
        btn.textContent = '✓ Ajouté !';
        btn.style.background = 'var(--green-lt)';
        const badge = document.querySelector('.badge-count');
        if (badge) badge.textContent = data.cart_count;
      } else {
        btn.textContent = data.error || 'Erreur';
      }
    } catch(e) { btn.textContent = 'Erreur réseau'; }
    setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.disabled = false; }, 2000);
  });
});

// ── Image preview (product-add form) ─────────────────────────
const imgInput = document.getElementById('product-images');
const imgPrev  = document.getElementById('image-preview');
if (imgInput && imgPrev) {
  imgInput.addEventListener('change', () => {
    imgPrev.innerHTML = '';
    [...imgInput.files].slice(0,4).forEach(file => {
      const url = URL.createObjectURL(file);
      const img = document.createElement('img');
      img.src = url; img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--border);';
      imgPrev.appendChild(img);
    });
  });
}

// ── Auto-dismiss alerts ───────────────────────────────────────
document.querySelectorAll('.alert').forEach(a => setTimeout(() => a.remove(), 5000));
