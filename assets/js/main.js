/**
 * Girish Pawar — Personal Developer Portfolio Main Script
 * Handles Interactive Navigation, Themes, Filters, Modals & Forms
 */

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initAnimatedHeadlines();
  initNavigation();
  initSkillsFilter();
  initProjectsFilter();
  initModals();
  initContactForm();
  initBackToTop();
  initClipboard();
});

/* ====================================================
   1. Theme Management (Dark / Light)
   ==================================================== */
function initTheme() {
  const themeToggleBtn = document.getElementById('theme-toggle-btn');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
  const savedTheme = localStorage.getItem('girish_theme') || (prefersDark.matches ? 'dark' : 'light');

  applyTheme(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      applyTheme(newTheme);
      localStorage.setItem('girish_theme', newTheme);
    });
  }

  prefersDark.addEventListener('change', (e) => {
    if (!localStorage.getItem('girish_theme')) {
      applyTheme(e.matches ? 'dark' : 'light');
    }
  });
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  const icon = document.querySelector('#theme-toggle-btn i');
  if (icon) {
    if (theme === 'dark') {
      icon.className = 'fas fa-sun';
    } else {
      icon.className = 'fas fa-moon';
    }
  }
}

/* ====================================================
   2. Animated Typing Headline
   ==================================================== */
function initAnimatedHeadlines() {
  const target = document.getElementById('hero-typed-text');
  if (!target) return;

  const rawTitles = target.getAttribute('data-titles');
  let titles = [];
  try {
    titles = JSON.parse(rawTitles);
  } catch (e) {
    titles = ['Web Developer', 'Software Developer', 'Full-Stack Developer'];
  }

  if (!titles || titles.length === 0) return;

  let titleIndex = 0;
  let charIndex = 0;
  let isDeleting = false;
  const typingSpeed = 90;
  const deletingSpeed = 45;
  const delayBetweenWords = 2000;

  function typeStep() {
    const currentWord = titles[titleIndex];
    if (isDeleting) {
      target.textContent = currentWord.substring(0, charIndex - 1);
      charIndex--;
    } else {
      target.textContent = currentWord.substring(0, charIndex + 1);
      charIndex++;
    }

    if (!isDeleting && charIndex === currentWord.length) {
      isDeleting = true;
      setTimeout(typeStep, delayBetweenWords);
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      titleIndex = (titleIndex + 1) % titles.length;
      setTimeout(typeStep, 400);
    } else {
      setTimeout(typeStep, isDeleting ? deletingSpeed : typingSpeed);
    }
  }

  typeStep();
}

/* ====================================================
   3. Interactive Navigation & Scroll Spy
   ==================================================== */
function initNavigation() {
  const header = document.querySelector('.site-header');
  const mobileToggle = document.getElementById('mobile-nav-toggle');
  const navLinks = document.getElementById('primary-nav-links');
  const links = document.querySelectorAll('.nav-item a');
  const sections = document.querySelectorAll('section[id]');

  // Scroll Header Shadow
  window.addEventListener('scroll', () => {
    if (window.scrollY > 30) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }

    // Scroll Spy
    let currentId = '';
    const scrollPos = window.scrollY + 120;

    sections.forEach(sec => {
      const top = sec.offsetTop;
      const height = sec.offsetHeight;
      if (scrollPos >= top && scrollPos < top + height) {
        currentId = sec.getAttribute('id');
      }
    });

    links.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === `#${currentId}`) {
        link.classList.add('active');
      }
    });
  });

  // Mobile Menu Toggle
  if (mobileToggle && navLinks) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('open');
      mobileToggle.setAttribute('aria-expanded', isOpen);
      const icon = mobileToggle.querySelector('i');
      if (icon) {
        icon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
      }
    });

    // Close mobile nav on link click
    links.forEach(l => {
      l.addEventListener('click', () => {
        navLinks.classList.remove('open');
        if (mobileToggle) {
          mobileToggle.setAttribute('aria-expanded', 'false');
          const icon = mobileToggle.querySelector('i');
          if (icon) icon.className = 'fas fa-bars';
        }
      });
    });
  }
}

/* ====================================================
   4. Skills Category Filter
   ==================================================== */
function initSkillsFilter() {
  const filterBtns = document.querySelectorAll('.skills-filter-nav .filter-btn');
  const skillCards = document.querySelectorAll('.skills-grid .skill-card');

  if (!filterBtns.length) return;

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const cat = btn.getAttribute('data-filter');

      skillCards.forEach(card => {
        const cardCat = card.getAttribute('data-category');
        if (cat === 'all' || cardCat === cat) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

/* ====================================================
   5. Projects Filtering & Search
   ==================================================== */
function initProjectsFilter() {
  const filterBtns = document.querySelectorAll('.project-filter-btn');
  const searchInput = document.getElementById('project-search-input');
  const projectCards = document.querySelectorAll('.projects-grid .project-card');

  function applyFilters() {
    const activeBtn = document.querySelector('.project-filter-btn.active');
    const selectedCat = activeBtn ? activeBtn.getAttribute('data-category') : 'all';
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

    let matchCount = 0;

    projectCards.forEach(card => {
      const category = card.getAttribute('data-category') || '';
      const title = (card.querySelector('.project-title')?.textContent || '').toLowerCase();
      const desc = (card.querySelector('.project-desc')?.textContent || '').toLowerCase();
      const tags = (card.getAttribute('data-tags') || '').toLowerCase();

      const matchesCat = (selectedCat === 'all' || category.toLowerCase() === selectedCat.toLowerCase());
      const matchesSearch = !query || title.includes(query) || desc.includes(query) || tags.includes(query);

      if (matchesCat && matchesSearch) {
        card.style.display = 'flex';
        matchCount++;
      } else {
        card.style.display = 'none';
      }
    });

    const emptyNotice = document.getElementById('no-projects-notice');
    if (emptyNotice) {
      emptyNotice.style.display = matchCount === 0 ? 'block' : 'none';
    }
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      applyFilters();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }
}

/* ====================================================
   6. Modals (Case Study & Certificate Viewer)
   ==================================================== */
function initModals() {
  const overlay = document.getElementById('main-modal-overlay');
  const closeBtn = document.getElementById('modal-close-btn');
  const modalBody = document.getElementById('modal-dynamic-content');

  if (!overlay || !closeBtn || !modalBody) return;

  function closeModal() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('active')) {
      closeModal();
    }
  });

  // Project Case Study Click
  document.querySelectorAll('.btn-open-case-study').forEach(btn => {
    btn.addEventListener('click', () => {
      const raw = btn.getAttribute('data-project');
      if (!raw) return;
      try {
        const p = JSON.parse(raw);
        renderProjectCaseStudy(p, modalBody);
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      } catch (err) {
        console.error('Failed to parse project data', err);
      }
    });
  });

  // Certificate Preview Click
  document.querySelectorAll('.cert-preview-img').forEach(el => {
    el.addEventListener('click', () => {
      const img = el.querySelector('img');
      const title = el.getAttribute('data-title') || 'Certificate of Achievement';
      if (img) {
        modalBody.innerHTML = `
          <div style="text-align: center;">
            <h3 style="font-family: var(--font-display); font-size: 1.5rem; margin-bottom: 1rem; color: var(--text-primary);">${escapeHtml(title)}</h3>
            <img src="${img.src}" alt="${escapeHtml(title)}" style="max-width: 100%; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
          </div>
        `;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });
}

function renderProjectCaseStudy(p, container) {
  let tagsHtml = '';
  if (Array.isArray(p.tech_stack)) {
    tagsHtml = p.tech_stack.map(t => `<span class="tech-tag">${escapeHtml(t)}</span>`).join(' ');
  }

  let linksHtml = '';
  if (p.github_url) {
    linksHtml += `<a href="${escapeHtml(p.github_url)}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm"><i class="fab fa-github"></i> GitHub Repository</a> `;
  }
  if (p.demo_url) {
    linksHtml += `<a href="${escapeHtml(p.demo_url)}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm"><i class="fas fa-external-link-alt"></i> Live Demo</a> `;
  }

  container.innerHTML = `
    <div class="case-study-view">
      <div class="edu-badge" style="margin-bottom: 0.5rem;">${escapeHtml(p.category)}</div>
      <h2 style="font-family: var(--font-display); font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.75rem;">${escapeHtml(p.title)}</h2>
      
      ${p.cover_image ? `<div style="margin: 1.25rem 0; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-subtle);"><img src="${escapeHtml(p.cover_image)}" style="width: 100%; height: auto; display: block;" alt="${escapeHtml(p.title)}"></div>` : ''}

      <div class="project-tags" style="margin-bottom: 1.5rem;">${tagsHtml}</div>

      <div style="color: var(--text-secondary); line-height: 1.7; font-size: 1rem; margin-bottom: 1.75rem;">
        <h4 style="color: var(--text-primary); font-size: 1.05rem; margin-bottom: 0.4rem;">Overview</h4>
        <p>${escapeHtml(p.short_description)}</p>
      </div>

      ${p.key_features ? `
        <div style="margin-bottom: 1.5rem;">
          <h4 style="color: var(--text-primary); font-size: 1.05rem; margin-bottom: 0.5rem;"><i class="fas fa-check-circle" style="color: var(--cyan-primary);"></i> Key Features</h4>
          <pre style="font-family: var(--font-sans); white-space: pre-line; color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6;">${escapeHtml(p.key_features)}</pre>
        </div>
      ` : ''}

      ${p.challenges ? `
        <div style="margin-bottom: 1.5rem; background: var(--bg-base); padding: 1.25rem; border-radius: var(--radius-md); border-left: 3px solid #f59e0b;">
          <h4 style="color: #f59e0b; font-size: 0.95rem; margin-bottom: 0.35rem;">Challenge</h4>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">${escapeHtml(p.challenges)}</p>
        </div>
      ` : ''}

      ${p.solutions ? `
        <div style="margin-bottom: 1.5rem; background: var(--bg-base); padding: 1.25rem; border-radius: var(--radius-md); border-left: 3px solid var(--cyan-primary);">
          <h4 style="color: var(--cyan-primary); font-size: 0.95rem; margin-bottom: 0.35rem;">Engineered Solution</h4>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">${escapeHtml(p.solutions)}</p>
        </div>
      ` : ''}

      ${p.results ? `
        <div style="margin-bottom: 1.5rem; background: var(--bg-base); padding: 1.25rem; border-radius: var(--radius-md); border-left: 3px solid var(--emerald-primary);">
          <h4 style="color: var(--emerald-primary); font-size: 0.95rem; margin-bottom: 0.35rem;">Results & Impact</h4>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">${escapeHtml(p.results)}</p>
        </div>
      ` : ''}

      <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        ${linksHtml}
      </div>
    </div>
  `;
}

/* ====================================================
   7. Functional Contact Form with Backend Validation
   ==================================================== */
function initContactForm() {
  const form = document.getElementById('portfolio-contact-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Check honeypot
    const honeypot = form.querySelector('input[name="website_hp"]');
    if (honeypot && honeypot.value.trim() !== '') {
      showToast('Form submission blocked.', 'danger');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending message...';

    const formData = new FormData(form);

    try {
      const response = await fetch('contact.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        showToast(result.message || 'Thank you! Your message has been sent successfully.', 'success');
        form.reset();
      } else {
        showToast(result.error || 'Failed to deliver message. Please check the fields.', 'danger');
      }
    } catch (err) {
      showToast('Network error while sending message. Please try again.', 'danger');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    }
  });
}

/* ====================================================
   8. Clipboard Copy
   ==================================================== */
function initClipboard() {
  document.querySelectorAll('.btn-copy-email').forEach(btn => {
    btn.addEventListener('click', () => {
      const email = btn.getAttribute('data-email');
      if (email) {
        navigator.clipboard.writeText(email).then(() => {
          showToast(`Email copied: ${email}`, 'success');
        }).catch(() => {
          showToast('Failed to copy to clipboard', 'danger');
        });
      }
    });
  });
}

/* ====================================================
   9. Back to Top Button
   ==================================================== */
function initBackToTop() {
  const btn = document.getElementById('btn-back-to-top');
  if (!btn) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ====================================================
   10. Toast Notification System
   ==================================================== */
function showToast(message, type = 'success') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
    <span>${escapeHtml(message)}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
