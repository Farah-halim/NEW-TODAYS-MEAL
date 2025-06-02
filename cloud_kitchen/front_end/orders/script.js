function updateStatus(select, status) {
  if (!status) return;

  if (status === 'delivered' && !confirm('Mark as delivered?')) {
    select.value = '';
    return;
  }

  const orderCard = select.closest('.order-card');
  const statusDisplay = orderCard.querySelector('.order-status');

  statusDisplay.className = 'order-status ' + status;
  statusDisplay.textContent = status.charAt(0).toUpperCase() + status.slice(1);
  orderCard.dataset.status = status;

  if (status === 'delivered') {
    select.disabled = true;
    select.style.opacity = '0.6';
  }

  updateInsights();
}

function updateInsights() {
  const orders = document.querySelectorAll('.order-card:not(.hidden)');
  const stats = Array.from(orders).reduce((acc, order) => {
    acc.total++;
    order.dataset.status === 'delivered' ? acc.delivered++ : acc.pending++;
    const total = parseFloat(order.querySelector('.info-item:last-child').textContent.replace('Total: $', '')) || 0;
    acc.revenue += total;
    return acc;
  }, { total: 0, pending: 0, delivered: 0, revenue: 0 });

  document.querySelectorAll('.insight-value').forEach((el, i) => {
    el.textContent = i === 3 ? `$${stats.revenue.toFixed(2)}` : 
                    i === 0 ? stats.total :
                    i === 1 ? stats.pending : stats.delivered;
  });
}

function updatePackageStatus(select, status) {
  if (!status) return;

  if (status === 'delivered') {
    if (!confirm('Are you sure you want to mark this package as delivered?')) {
      select.value = '';
      return;
    }
  }

  const packageContainer = select.closest('.package');
  const packageItems = packageContainer.querySelector('.package-items');

  if (status === 'delivered') {
    packageItems.style.textDecoration = 'line-through';
    packageItems.style.color = '#999';
    packageItems.style.opacity = '0.7';
  } else {
    packageItems.style.textDecoration = 'none';
    packageItems.style.color = '';
    packageItems.style.opacity = '';
  }

  const orderCard = select.closest('.order-card');
  updateDeliveryCount(orderCard);

  // Check if all packages are delivered
  const packages = orderCard.querySelectorAll('.package');
  const allDelivered = Array.from(packages).every(pkg => {
    const activeBtn = pkg.querySelector('.status-btn.active');
    return activeBtn && activeBtn.classList.contains('delivered');
  });

  if (allDelivered) {
    const orderDeliveredBtn = orderCard.querySelector('.status-buttons .status-btn.delivered');
    updateStatus(orderDeliveredBtn, 'delivered');
  }
}

function updateDeliveryCount(orderCard) {
  if (!orderCard) return;

  const deliveryType = orderCard.querySelector('.delivery-type');
  if (!deliveryType || deliveryType.textContent.includes('Single Delivery')) return;

  const packages = orderCard.querySelectorAll('.package');
  const totalPackages = packages.length;
  let deliveredPackages = 0;

  packages.forEach(pkg => {
    const activeBtn = pkg.querySelector('.status-btn.active');
    if (activeBtn && activeBtn.classList.contains('delivered')) {
      deliveredPackages++;
    }
  });

  const typeText = deliveryType.textContent.split('(')[0].trim();
  deliveryType.textContent = `${typeText} (${deliveredPackages}/${totalPackages})`;
}

function filterByDate(selectedDate) {
  const formattedDate = selectedDate ? new Date(selectedDate).toLocaleDateString('en-US', {
    year: 'numeric', month: 'long', day: 'numeric'
  }) : '';

  document.querySelectorAll('.order-card').forEach(card => {
    const dates = card.querySelectorAll('.package-header span');
    card.classList.toggle('hidden', 
      selectedDate && !Array.from(dates).some(date => date.textContent.includes(formattedDate))
    );
  });
  updateInsights();
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.order-card').forEach(card => {
    card.addEventListener('click', (e) => {
      if (!e.target.closest('.status-buttons, .status-select')) {
        card.classList.toggle('expanded');
      }
    });
  });

  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      this.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const status = document.querySelector('.filter-btn[data-status].active')?.dataset.status || 'all';
      const type = document.querySelector('.filter-btn[data-filter].active')?.dataset.filter || 'all';

      document.querySelectorAll('.order-card').forEach(card => {
        card.classList.toggle('hidden',
          (status !== 'all' && status !== card.dataset.status) ||
          (type !== 'all' && type !== card.dataset.type)
        );
      });
      updateInsights();
    });
  });

  const scheduledOrders = document.querySelectorAll('.order-card[data-type="scheduled"]');
  scheduledOrders.forEach(updateDeliveryCount);

  updateInsights();
});

function toggleReferenceImage(button) {
  const image = button.nextElementSibling;
  const isHidden = image.classList.contains('hidden');

  image.classList.toggle('hidden');
  button.textContent = isHidden ? 'Hide Reference Image' : 'Show Reference Image';
}