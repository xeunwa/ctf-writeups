$(document).ready(function() {
  // Extract the item ID from the URL path.
  // For example, if the URL is /item/1, then itemId will be "1"
  const pathParts = window.location.pathname.split('/');
  const itemId = pathParts[pathParts.length - 1];
  if (!itemId) {
    alert('No item specified.');
    return;
  }

  // Load item details from the API.
  $.ajax({
    url: '/api/item/' + itemId,
    method: 'GET',
    success: function(item) {
      const itemHtml = `
        <section class="item-detail-card magical-border">
          <div class="item-detail-image">
            <img src="${item.imageUrl}" alt="${item.name}">
          </div>
          <div class="item-detail-content">
            <h1 class="magical-glow">${item.name}</h1>
            <p>${item.description}</p>
            <p class="seller-info">Sold by: ${item.seller.username}</p>
            <div class="item-detail-stats">
              <div class="stat">
                <i data-lucide="coins"></i>
                <span>${Number(item.currentBid).toLocaleString()} Gold</span>
              </div>
              <div class="stat">
                <i data-lucide="clock"></i>
                <span>${item.timeLeft}</span>
              </div>
            </div>
            <div class="bid-controls">
              <input type="number" id="bidAmount" placeholder="Enter bid amount" min="${item.currentBid + 100}" step="100">
              <button class="btn-primary" id="placeBid">Place Bid</button>
            </div>
          </div>
        </section>
      `;
      $('#itemDetail').html(itemHtml);
      lucide.createIcons();
      loadBids(itemId);
    },
    error: function(err) {
      console.error('Error fetching item details:', err);
    }
  });

  // Function to load bids for the given item.
  function loadBids(itemId) {
    $.ajax({
      url: '/api/items/' + itemId + '/bids',
      method: 'GET',
      success: function(bids) {
        $('#bidsList').empty();
        bids.forEach(function(bid) {
          const bidHtml = `
            <div class="bid-item">
              <div class="bid-info">
                <span>${bid.user.username}</span>
                <span>${Number(bid.amount).toLocaleString()} Gold</span>
              </div>
              <span>${new Date(bid.createdAt).toLocaleString()}</span>
            </div>
          `;
          $('#bidsList').append(bidHtml);
        });
      },
      error: function(err) {
        console.error('Error fetching bids:', err);
      }
    });
  }

  // Handle bid submission.
  $(document).on('click', '#placeBid', function() {
    const bidAmount = parseInt($('#bidAmount').val(), 10);
    if (isNaN(bidAmount)) {
      alert('Please enter a valid bid amount.');
      return;
    }
    $.ajax({
      url: '/api/bid',
      method: 'POST',
      data: { itemId: itemId, amount: bidAmount },
      success: function(response, textStatus, xhr) {
        if (response.success) {
          alert('Bid placed successfully!');
          loadBids(itemId);
        }
      },
      error: function(err, textStatus, xhr) {
        if (xhr == 'Forbidden') {
          window.location.replace('/login');
        } else {
          alert('Error: ' + (err.responseJSON ? err.responseJSON.error : 'Bid submission failed.'));
        }
      }
    });
  });
});
