@extends("layouts.master")
@section("content")

<div class="container-fluid">
  <!-- Search Section -->
  <div class="row search-section">
    <div class="col-md-5 offset-md-1">
      <div class="search-form-outline">
        <i class="fas fa-briefcase"></i>
        <input type="text" id="jobSearchTitle" class="form-control" placeholder="" required>
        <label for="jobSearchTitle">Job Title, Skills, or Company</label>
      </div>
    </div>
    <div class="col-md-4">
      <div class="search-form-outline">
        <i class="fas fa-map-marker-alt"></i>
        <input type="text" id="jobSearchLocation" class="form-control" placeholder="" required>
        <label for="jobSearchLocation">Location</label>
      </div>
    </div>
    <div class="col-md-2 d-flex align-items-center">
      <button type="submit" class="btn btn-primary btn-search w-100">Search</button>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="row filter-section">
    <div class="col-md-10 offset-md-1">
      <button class="btn btn-outline-primary filter-btn active" data-filter="all">All</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="full-time">Full-time</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="part-time">Part-time</button>
      <button class="btn btn-outline-primary filter-btn" data-filter="remote">Remote</button>
    </div>
  </div>

  <!-- Job Listings -->
  <div class="row">
    <div class="col-md-5 offset-md-1">
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
    <div class="col-md-6">
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