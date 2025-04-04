$(document).ready(function() {
  // Function to load items from the API using the /api/filter endpoint.
  function loadItems(prismaFilter = {}) {
    $.ajax({
      url: '/api/filter',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ filter: prismaFilter }),
      success: function(items) {
        $('#itemsGrid').empty();
        items.forEach(function(item) {
          const itemCard = `
            <div class="item-card magical-border">
              <div class="item-image">
                <img src="${item.imageUrl}" alt="${item.name}">
              </div>
              <div class="item-content">
                <h3 class="magical-glow">${item.name}</h3>
                <p>${item.description}</p>
                <div class="item-stats">
                  <div class="stat">
                    <i data-lucide="coins"></i>
                    <span>${Number(item.currentBid).toLocaleString()} Gold</span>
                  </div>
                  <div class="stat">
                    <i data-lucide="clock"></i>
                    <span>${item.timeLeft}</span>
                  </div>
                </div>
                <button class="btn-primary view-details" data-id="${item.id}">
                  View Details
                </button>
              </div>
            </div>
          `;
          $('#itemsGrid').append(itemCard);
        });
        // Reinitialize icons (if using lucide)
        lucide.createIcons();
      },
      error: function(err) {
        console.error('Error fetching items:', err);
      }
    });
  }

  // Initial load with an empty "where" clause.
  loadItems({ where: {} });

  // Handle filter form submission using POST to /api/filter.
  $('#filterForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serializeArray();
    let filterObj = {};

    formData.forEach(function(field) {
      if (field.value) {
        // Convert numeric or range-based fields as needed.
        if (field.name === "levelRequirement") {
          if (field.value === "Under 20") {
            filterObj["levelRequirement"] = { lt: 20 };
          } else if (field.value === "20-40") {
            filterObj["levelRequirement"] = { gte: 20, lte: 40 };
          } else if (field.value === "Over 40") {
            filterObj["levelRequirement"] = { gt: 40 };
          }
        } else if (field.name === "weight") {
          if (field.value === "Under 5") {
            filterObj["weight"] = { lt: 5 };
          } else if (field.value === "5-10") {
            filterObj["weight"] = { gte: 5, lte: 10 };
          } else if (field.value === "Over 10") {
            filterObj["weight"] = { gt: 10 };
          }
        } else {
          // For all other fields, use the value as a string.
          filterObj[field.name] = field.value;
        }
      }
    });

    // Pass the crafted filter wrapped in a "where" clause to the API.
    loadItems({ where: filterObj });
  });

  // When "View Details" is clicked, redirect to /item/<id>
  $(document).on('click', '.view-details', function() {
    const itemId = $(this).data('id');
    window.location.href = '/item/' + itemId;
  });
});
