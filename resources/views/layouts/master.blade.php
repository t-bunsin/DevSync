<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Default Title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/fontawesome.min.css">

    <!-- CSS assets -->
    <link href="{{ asset('css/fstyle.css') }}" rel="stylesheet" />
</head>
<body>
    @include('partials.header')
    
    <main class="container">
        @yield('content')
    </main>
    
    @include('partials.footer')
    
    <!-- JavaScript assets -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>