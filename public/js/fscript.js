document.addEventListener("DOMContentLoaded", function () {
    const jobCards = document.querySelectorAll(".job-card");
    const filterButtons = document.querySelectorAll(".filter-btn");
    const descCard = document.getElementById("job-description");

    // Job Card Click Handler
    jobCards.forEach(card => {
      card.addEventListener("click", function () {
        descCard.classList.remove("d-none");
        document.getElementById("desc-title").textContent = card.dataset.title;
        document.getElementById("desc-location").textContent = `${card.dataset.company} • ${card.dataset.location}`;
        document.getElementById("desc-salary").textContent = card.dataset.salary;
        document.getElementById("desc-text").innerHTML = card.dataset.description;

        // Scroll to description on mobile
        if (window.innerWidth <= 768) {
          descCard.scrollIntoView({ behavior: "smooth" });
        }
      });
    });

    // Filter Handler
    filterButtons.forEach(button => {
      button.addEventListener("click", function () {
        filterButtons.forEach(btn => btn.classList.remove("active"));
        button.classList.add("active");

        const filter = button.dataset.filter;
        jobCards.forEach(card => {
          if (filter === "all" || card.dataset.type === filter) {
            card.style.display = "block";
          } else {
            card.style.display = "none";
          }
        });

        // Hide description if no jobs are visible
        if (!document.querySelector(".job-card[style='display: block;']")) {
          descCard.classList.add("d-none");
        }
      });
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    // Persist advanced search state
    const advancedSearch = localStorage.getItem('advancedSearch');
    if (advancedSearch === 'true') {
        document.querySelector('.advanced-search-toggle').click();
    }
    
    document.querySelector('.advanced-search-toggle').addEventListener('click', function() {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        localStorage.setItem('advancedSearch', !isExpanded);
    });
    
    // Add autocomplete for locations
    if (document.getElementById('search_location')) {
        new Autocomplete(document.getElementById('search_location'), {
            threshold: 2,
            maximumItems: 5,
            onSelectItem: ({label, value}) => {
                console.log("selected location:", label, value);
            }
        });
    }