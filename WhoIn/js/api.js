/* ============================================
   WhoIn — AJAX Operations & Core API
   ============================================ */

// ---------- User Retrieval (AJAX) ----------
function retrieveUser() {
  const idInput = document.getElementById('search_id');
  if (!idInput) return;

  const id_number = idInput.value.trim();
  if (!id_number) {
    ToastManager.show('Please enter an ID number.', 'warning');
    return;
  }

  const userInfo = document.getElementById('user_info');
  if (!userInfo) return;

  // Show loading shimmer
  userInfo.innerHTML = `
    <div class="user-card" style="animation: none; opacity: 0.6;">
      <div class="shimmer shimmer-line" style="width: 40%; height: 20px;"></div>
      <div class="shimmer shimmer-line short" style="height: 14px;"></div>
      <div class="shimmer shimmer-line short" style="height: 14px; width: 50%;"></div>
    </div>
  `;

  fetch('get_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id_number=' + encodeURIComponent(id_number)
  })
    .then(res => res.json())
    .then(data => {
      if (data.exists) {
        userInfo.innerHTML = `
          <div class="user-card">
            <h5>${escapeHtml(data.first_name)} ${escapeHtml(data.last_name)}</h5>
            <p>${escapeHtml(data.email)}<br>${escapeHtml(data.contact_number)}</p>
            <div class="user-card-actions">
              <button class="btn-success-dark" onclick="signIn(${data.user_id})">Sign In</button>
              <button class="btn-danger-dark" onclick="signOut(${data.user_id})">Sign Out</button>
            </div>
          </div>
        `;
      } else {
        userInfo.innerHTML = `
          <div class="alert-dark warning">
            User not found. Please register first.
          </div>
        `;
      }
    })
    .catch(() => {
      userInfo.innerHTML = `
        <div class="alert-dark warning">
          Error retrieving user. Please try again.
        </div>
      `;
    });
}

// ---------- Sign In ----------
function signIn(user_id) {
  fetch('sign_in.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'user_id=' + encodeURIComponent(user_id)
  })
    .then(res => res.text())
    .then(response => {
      if (response.includes('already')) {
        ToastManager.show('User is already signed in.', 'info');
      } else {
        ToastManager.show('Signed in successfully!', 'success');
      }
      loadActiveSessions();
      // Clear search
      const userInfo = document.getElementById('user_info');
      if (userInfo) userInfo.innerHTML = '';
      const searchInput = document.getElementById('search_id');
      if (searchInput) searchInput.value = '';
    })
    .catch(() => {
      ToastManager.show('Error signing in. Please try again.', 'error');
    });
}

// ---------- Sign Out ----------
function signOut(user_id) {
  fetch('sign_out.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'user_id=' + encodeURIComponent(user_id)
  })
    .then(res => res.text())
    .then(() => {
      ToastManager.show('Signed out successfully!', 'success');
      loadActiveSessions();
      // Clear search
      const userInfo = document.getElementById('user_info');
      if (userInfo) userInfo.innerHTML = '';
      const searchInput = document.getElementById('search_id');
      if (searchInput) searchInput.value = '';
    })
    .catch(() => {
      ToastManager.show('Error signing out. Please try again.', 'error');
    });
}

// ---------- Load Active Sessions ----------
function loadActiveSessions() {
  const container = document.getElementById('active_sessions');
  if (!container) return;

  fetch('active_sessions.php')
    .then(res => res.text())
    .then(html => {
      // Smooth transition
      container.style.opacity = '0';
      container.style.transform = 'translateY(8px)';

      setTimeout(() => {
        container.innerHTML = html;
        container.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0)';

        // Update counter
        updateActiveCount();
      }, 150);
    })
    .catch(() => {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon"></div>
          <p class="empty-state-text">Unable to load sessions</p>
        </div>
      `;
    });
}

// ---------- Update Active Count ----------
function updateActiveCount() {
  const container = document.getElementById('active_sessions');
  const counterEl = document.getElementById('active_count');
  if (!container || !counterEl) return;

  const items = container.querySelectorAll('.active-list-item');
  const count = items.length;

  const currentCount = parseInt(counterEl.textContent) || 0;
  if (currentCount !== count) {
    animateCounter(counterEl, count);
  }
}

// ---------- HTML Escape ----------
function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// ---------- Enter Key Support ----------
function initEnterKeySupport() {
  const searchInput = document.getElementById('search_id');
  if (searchInput) {
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        retrieveUser();
      }
    });
  }
}
