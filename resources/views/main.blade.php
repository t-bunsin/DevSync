@extends("layouts.master")
@section("content")

<div class="container-fluid">
  <!-- Search Section -->
  <div class="job-search-container bg-white rounded-3 shadow-sm p-4 mb-5">
    <form action="" method="GET" class="row g-3 align-items-end">
        @csrf
        
        <!-- Keyword Search -->
        <div class="col-lg-5 col-md-6">
            <div class="form-floating has-icon">
                <input type="text" 
                       id="search_query" 
                       name="query"
                       class="form-control" 
                       placeholder="Job title, keywords, or company" 
                       value=""
                       aria-label="Search by job title, keywords, or company">
                <label for="search_query">Job title, keywords, or company</label>
                <span class="input-icon">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
        
        <!-- Location Search -->
        <div class="col-lg-4 col-md-6">
            <div class="form-floating has-icon">
                <input type="text" 
                       id="search_location" 
                       name="location"
                       class="form-control" 
                       placeholder="City, state, or remote" 
                       value=""
                       aria-label="Search by location">
                <label for="search_location">City, state, or remote</label>
                <span class="input-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </span>
            </div>
        </div>
        
        <!-- Job Type Dropdown -->
        <div class="col-lg-2 col-md-4">
            <div class="form-floating">
                <select class="form-select" 
                        id="job_type" 
                        name="type"
                        aria-label="Filter by job type">
                    <option value="">All Types</option>
                </select>
                <label for="job_type">Job Type</label>
            </div>
        </div>
        
        <!-- Search Button -->
        <div class="col-lg-1 col-md-4 d-grid">
            <button type="submit" 
                    class="btn btn-primary btn-lg h-100 py-3" 
                    aria-label="Search jobs">
                <i class="fas fa-search"></i>
                <span class="d-none d-lg-inline">Search</span>
            </button>
        </div>
        
        <!-- Advanced Search Toggle -->
        <div class="col-12">
            <a href="#advanced-search" 
               class="text-decoration-none small advanced-search-toggle" 
               data-bs-toggle="collapse" 
               aria-expanded="false" 
               aria-controls="advanced-search">
                <i class="fas fa-sliders-h me-1"></i> Advanced Search
            </a>
        </div>
        
        <!-- Advanced Search Options -->
        <div class="collapse mt-3" id="advanced-search">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="salary_range" class="form-label">Salary Range</label>
                    <select class="form-select" id="salary_range" name="salary_range">
                        <option value="">Any salary</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="experience_level" class="form-label">Experience Level</label>
                    <select class="form-select" id="experience_level" name="experience_level">
                        <option value="">Any experience</option>                    
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date_posted" class="form-label">Date Posted</label>
                    <select class="form-select" id="date_posted" name="date_posted">
                        <option value="">Any time</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>

  <!-- Job Listings -->
  <div class="row">
    <div class="col-md-5">
      <div class="job-list-container">
        <!-- Job Card 1 -->
        <div class="job-card"
             data-title="Retail Associates"
             data-company="Hobby Lobby"
             data-location="Walla Walla, WA"
             data-salary="$19.25 - $20.25 an hour"
             data-type="full-time"
             data-urgency="urgent"
             data-description="<strong>Immediate Openings!</strong><br>Join our team to assist customers, manage inventory, and create a welcoming store environment. Competitive pay, benefits, and a vibrant work culture await you!">
          <div class="urgency-flag">Urgent</div>
          <div class="card-body">
            <div class="card-img-actions">
              <a href="#">
                <img src="https://shreethemes.in/jobseeker/assets/images/company/spotify.png" alt="Company Logo" class="card-img">
              </a>
            </div>
            <div class="job-content">
              <h5 class="job-title">
                <a href="#" class="text-default">Retail Associates</a>
              </h5>
              <div class="job-meta">
                <ul class="list-inline list-inline-dotted text-muted mb-0">
                  <li class="list-inline-item">By <a href="#" class="text-muted">Hobby Lobby</a></li>
                  <li class="list-inline-item">Walla Walla, WA</li>
                </ul>
              </div>
              <p class="job-description">
                $19.25 - $20.25 an hour. Join our team to assist customers, manage inventory, and create a welcoming store environment. <a href="#">[...]</a>
              </p>
            </div>
          </div>
          <div class="card-footer">
            <ul>
              <li><i class="fas fa-briefcase"></i> Full-time</li>
              <li><i class="fas fa-clock"></i> 5 days left</li>
            </ul>
            <a href="#" class="apply-button"><i class="fas fa-check"></i> Apply Now</a>
          </div>
        </div>
        <!-- Job Card 2 -->
        <div class="job-card"
             data-title="Software Engineer"
             data-company="Tech Corp"
             data-location="Remote"
             data-salary="$80,000 - $120,000 a year"
             data-type="remote"
             data-urgency="high"
             data-description="<strong>Join Our Tech Team!</strong><br>We are looking for a skilled Software Engineer to develop innovative solutions. Work remotely with a dynamic team. Great benefits and growth opportunities.">
          <div class="urgency-flag">High</div>
          <div class="card-body">
            <div class="card-img-actions">
              <a href="#">
                <img src="https://shreethemes.in/jobseeker/assets/images/company/spotify.png" alt="Company Logo" class="card-img">
              </a>
            </div>
            <div class="job-content">
              <h5 class="job-title">
                <a href="#" class="text-default">Software Engineer</a>
              </h5>
              <div class="job-meta">
                <ul class="list-inline list-inline-dotted text-muted mb-0">
                  <li class="list-inline-item">By <a href="#" class="text-muted">Tech Corp</a></li>
                  <li class="list-inline-item">Remote</li>
                </ul>
              </div>
              <p class="job-description">
                $80k - $120k a year. Work remotely with a dynamic team to develop innovative solutions. <a href="#">[...]</a>
              </p>
            </div>
          </div>
          <div class="card-footer">
            <ul>
              <li><i class="fas fa-globe"></i> Remote</li>
              <li><i class="fas fa-clock"></i> 10 days left</li>
            </ul>
            <a href="#" class="apply-button"><i class="fas fa-check"></i> Apply Now</a>
          </div>
        </div>
        <!-- Job Card 3 -->
        <div class="job-card"
             data-title="Graphic Designer"
             data-company="Creative Studio"
             data-location="Seattle, WA"
             data-salary="$30 - $40 an hour"
             data-type="part-time"
             data-urgency="medium"
             data-description="<strong>Unleash Your Creativity!</strong><br>We need a talented Graphic Designer for part-time projects. Collaborate on exciting designs with a passionate team. Flexible hours and great pay.">
          <div class="urgency-flag">Medium</div>
          <div class="card-body">
            <div class="card-img-actions">
              <a href="#">
                <img src="https://shreethemes.in/jobseeker/assets/images/company/spotify.png" alt="Company Logo" class="card-img">
              </a>
            </div>
            <div class="job-content">
              <h5 class="job-title">
                <a href="#" class="text-default">Graphic Designer</a>
              </h5>
              <div class="job-meta">
                <ul class="list-inline list-inline-dotted text-muted mb-0">
                  <li class="list-inline-item">By <a href="#" class="text-muted">Creative Studio</a></li>
                  <li class="list-inline-item">Seattle, WA</li>
                </ul>
              </div>
              <p class="job-description">
                $30 - $40 an hour. Collaborate on exciting designs with a passionate team. Flexible hours. <a href="#">[...]</a>
              </p>
            </div>
          </div>
          <div class="card-footer">
            <ul>
              <li><i class="fas fa-clock"></i> Part-time</li>
              <li><i class="fas fa-clock"></i> 3 days left</li>
            </ul>
            <a href="#" class="apply-button"><i class="fas fa-check"></i> Apply Now</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Job Description Panel -->
    <div class="col-md-7">
      <div class="card job-details d-none" id="job-description">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <h5 id="desc-title">Job Title</h5>
            <div class="text-muted" id="desc-location">Company • Location</div>
            <div class="mt-1" id="desc-salary">$X - $Y</div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-save"><i class="fas fa-heart"></i> Save</button>
            <button class="btn btn-apply">Apply Now</button>
          </div>
        </div>
        <hr>
        <div id="desc-text" class="text-dark"></div>
      </div>
    </div>
  </div>
</div>

@endsection