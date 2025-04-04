// public/js/main.js

// Vue instance for My Bids page
function initMyBidsVue() {
  new Vue({
    el: "#my_bids",
    delimiters: ['${', '}'],
    data: {
    },
    computed: {
    },
    methods: {
    }
  });
}

// Vue instance for My Submissions page
function initMySubmissionsVue() {
  const submissionsDataEl = document.getElementById("submissions-data");
  let submissions = [];
  if (submissionsDataEl) {
    try {
      submissions = JSON.parse(submissionsDataEl.getAttribute("data-submissions"));
    } catch (e) {
      console.error("Error parsing submissions data", e);
    }
  }
  new Vue({
    el: "#my-submissions-app",
    delimiters: ['${', '}'],
    data: {
      submissions: submissions,
      sortKey: '',
      sortOrders: {}
    },
    computed: {
      sortedSubmissions() {
        if (!this.sortKey) return this.submissions;
        return this.submissions.slice().sort((a, b) => {
          let order = this.sortOrders[this.sortKey] || 1;
          if (a[this.sortKey] < b[this.sortKey]) return -order;
          if (a[this.sortKey] > b[this.sortKey]) return order;
          return 0;
        });
      }
    },
    methods: {
      sortBy(key) {
        this.sortKey = key;
        this.sortOrders[key] = this.sortOrders[key] ? -this.sortOrders[key] : 1;
      }
    }
  });
}

// Vue instance for Dashboard page (showing auctions)
function initDashboardVue() {
  const auctionsDataEl = document.getElementById("auctions-data");
  let auctions = [];
  if (auctionsDataEl) {
    try {
      auctions = JSON.parse(auctionsDataEl.getAttribute("data-auctions"));
    } catch (e) {
      console.error("Error parsing auctions data", e);
    }
  }
  new Vue({
    el: "#dashboard-panel",
    delimiters: ['${', '}'],
    data: {
      auctions: auctions,
      sortKey: '',
      sortOrders: {}
    },
    computed: {
      sortedAuctions() {
        if (!this.sortKey) return this.auctions;
        return this.auctions.slice().sort((a, b) => {
          let order = this.sortOrders[this.sortKey] || 1;
          if (a[this.sortKey] < b[this.sortKey]) return -order;
          if (a[this.sortKey] > b[this.sortKey]) return order;
          return 0;
        });
      }
    },
    methods: {
      sortBy(key) {
        this.sortKey = key;
        this.sortOrders[key] = this.sortOrders[key] ? -this.sortOrders[key] : 1;
      }
    }
  });
}

// Vue instance for Login page
function initLoginVue() {
  new Vue({
    el: "#login-panel",
    delimiters: ['${', '}'],
    data: {
      username: "",
      password: "",
      loading: false,
      error: "",
    },
    methods: {
      async handleLogin() {
        this.loading = true;
        this.error = "";
        try {
          const response = await axios.post("/api/login", {
            username: this.username,
            password: this.password,
          });
          if (response.data.success) {
            window.location.href = "/";
          } else {
            this.error = response.data.message || "An error occurred.";
          }
        } catch (err) {
          console.error("Login error:", err);
          this.error = err.response?.data?.message || "An error occurred during login.";
        } finally {
          this.loading = false;
        }
      },
      async handleOAuthLogin() {
        try {
          const configResponse = await axios.get("/api/config");
          const clientId = configResponse.data.oauthClientId;
          const redirectUri = encodeURIComponent("/callback");
          const responseType = "code";
          const scope = "read";
          const authUrl = `/oauth/authorize?response_type=${responseType}&client_id=${clientId}&redirect_uri=${redirectUri}&scope=${encodeURIComponent(scope)}`;
          window.location.href = authUrl;
        } catch (error) {
          console.error("Error fetching OAuth config:", error);
          this.error = "Failed to retrieve OAuth configuration.";
        }
      },
    },
  });
}

// Vue instance for Submission page with input validation
function initSubmitVue() {
  new Vue({
    el: ".submit-panel",
    delimiters: ['${', '}'],
    data: {
      form: {
        name: '',
        description: '',
        url: '',
        category: 'lore',
      },
      showMessage: false,
      submitting: false,
      error: null,
    },
    methods: {
      async handleSubmit() {
        // Input validation: name and category must be alphanumeric (letters, numbers, spaces)
        const alphanumericRegex = /^[a-zA-Z0-9\s]+$/;
        if (!alphanumericRegex.test(this.form.name)) {
          this.error = "Name must contain only alphanumeric characters and spaces.";
          return;
        }
        if (!alphanumericRegex.test(this.form.category)) {
          this.error = "Category must contain only alphanumeric characters and spaces.";
          return;
        }
        this.submitting = true;
        this.error = null;
        try {
          const res = await axios.post('/api/submissions', this.form);
          if (res.data.success) {
            this.showMessage = true;
            // Reset form
            this.form = {
              name: '',
              description: '',
              url: '',
              category: 'lore',
            };
            setTimeout(() => {
              this.showMessage = false;
              window.location.href = '/';
            }, 3000);
          } else {
            this.error = res.data.message || 'An error occurred.';
          }
        } catch (err) {
          console.error('Submission error:', err);
          this.error = err.response?.data?.message || 'An error occurred during submission.';
        } finally {
          this.submitting = false;
        }
      },
    },
  });
}

// Vue instance for Auction creation page (admin)
function initAuctionVue() {
  new Vue({
    el: "#auction-panel",
    delimiters: ['${', '}'],
    data: {
      newAuction: {
        resourceId: '',
        startingBid: '',
        endTime: ''
      },
      creatingAuction: false,
      createError: ''
    },
    methods: {
      createAuction() {
        this.creatingAuction = true;
        this.createError = '';
        axios.post('/api/auctions', this.newAuction)
          .then(response => {
            if (response.data.success) {
              window.location.reload();
            } else {
              this.createError = response.data.message || 'Failed to create auction.';
            }
          })
          .catch(err => {
            console.error('Error creating auction:', err);
            this.createError = err.response?.data?.message || 'An error occurred.';
          })
          .finally(() => {
            this.creatingAuction = false;
          });
      }
    },
    mounted() {
      // Data is rendered server-side.
    }
  });
}

// Vue instance for Auction Details page (for placing a bid)
function initAuctionDetailsVue() {
  const container = document.getElementById('auction-details-panel');
  if (!container) return;

  // Read and parse the auction data passed from the server
  let auctionData = {};
  try {
    auctionData = JSON.parse(container.getAttribute('data-auction'));
  } catch (e) {
    console.error("Error parsing auction data", e);
  }

  new Vue({
    el: "#auction-details-panel",
    delimiters: ['${', '}'],
    data: {
      auctionData: auctionData,
      bidAmount: null,
      submitting: false,
      errorMessage: "",
      successMessage: ""
    },
    methods: {
      async submitBid() {
        if (!this.bidAmount) {
          this.errorMessage = "Bid amount is required.";
          return;
        }
        this.submitting = true;
        this.errorMessage = "";
        this.successMessage = "";
        try {
          const res = await axios.post(`/api/auctions/${this.auctionData.id}/bids`, {
            bid: this.bidAmount
          });
          if (res.data.success) {
            // Optionally, you could update auctionData.bids here without reloading;
            // for now we simply reload the page to fetch updated data.
            window.location.reload();
          } else {
            this.errorMessage = res.data.message || "Failed to place bid.";
          }
        } catch (err) {
          console.error("Error placing bid:", err);
          this.errorMessage = err.response?.data?.message || "Error placing bid.";
        } finally {
          this.submitting = false;
        }
      }
    }
  });
}


// Vue instance for OAuth Callback page
function initCallbackVue() {
  new Vue({
    el: "#callback-panel",
    delimiters: ['${', '}'],
    data: {
      loading: true,
      error: "",
    },
    mounted() {
      this.exchangeCodeForToken();
    },
    methods: {
      async exchangeCodeForToken() {
        const params = new URLSearchParams(window.location.search);
        const code = params.get('code');
        if (!code) {
          this.error = "No authorization code found in the URL.";
          this.loading = false;
          return;
        }
        try {
          const res = await axios.post("/api/oauthLogin", { code });
          if (res.data.success) {
            window.location.href = "/";
          } else {
            this.error = res.data.message || "OAuth login failed.";
          }
        } catch (err) {
          console.error("OAuth callback error:", err);
          this.error = err.response?.data?.message || "OAuth login failed.";
        } finally {
          this.loading = false;
        }
      },
    },
  });
}

// Vue instance for Admin panel
function initAdminVue() {
  new Vue({
    el: "#admin-panel",
    delimiters: ["${", "}"],
    data: {
      mode: "tables", // "tables" mode shows table list; "results" mode shows table data
      tables: [],
      results: [],
      columns: [],
      errorMessage: "",
    },
    created() {
      // On load, fetch the list of tables.
      this.fetchTables();
    },
    methods: {
      async fetchTables() {
        try {
          const response = await axios.get("/tables");
          if (response.data.success) {
            this.tables = response.data.tables;
          } else {
            this.errorMessage =
              response.data.message || "Could not load tables.";
          }
        } catch (err) {
          console.error("Error fetching tables:", err);
          this.errorMessage = "Error fetching table list.";
        }
      },
      async viewTable(tableName) {
        this.errorMessage = "";
        this.results = [];
        this.columns = [];
        try {
          const response = await axios.post("/table", { tableName });
          if (response.data.success) {
            this.results = response.data.results;
            if (this.results.length > 0) {
              this.columns = Object.keys(this.results[0]);
            }
            this.mode = "results";
          } else {
            this.errorMessage =
              response.data.message || "Could not load table data.";
          }
        } catch (err) {
          console.error("Error fetching table data:", err);
          this.errorMessage = "Error fetching table data.";
        }
      },
      backToTables() {
        this.mode = "tables";
        this.errorMessage = "";
        this.results = [];
        this.columns = [];
      },
    },
  });
}


// AUTO-INIT
document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('#login-panel')) {
    initLoginVue();
  }
  if (document.querySelector('#dashboard-panel')) {
    initDashboardVue();
  }
  if (document.querySelector('.submit-panel')) {
    initSubmitVue();
  }
  if (document.querySelector('#auction-panel')) {
    initAuctionVue();
  }
  if (document.querySelector('#auction-details-panel')) {
    initAuctionDetailsVue();
  }
  if (document.querySelector('#callback-panel')) {
    initCallbackVue();
  }
  if (document.querySelector('#my_bids')) {
    initMyBidsVue();
  }
  if (document.querySelector('#my-submissions-app')) {
    initMySubmissionsVue();
  }
  if (document.querySelector("#admin-panel")) {
    initAdminVue();
  }
});
