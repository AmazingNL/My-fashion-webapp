<?php
/** @var array $data */
$title = $data['title'] ?? 'About Us';
?>

<section class="about-hero">
  <div class="about-hero__inner">
    <div class="badge">Afro Elegance</div>
    <h1 class="about-hero__title"><?= htmlspecialchars($title) ?></h1>
    <p class="about-hero__lead">
      This project blends culture-inspired design with clean MVC engineering: a custom clothing webshop
      powered by secure PHP, structured code, and a user-first experience.
    </p>

    <div class="about-hero__actions">
      <a class="btn btn--primary" href="/products">Explore Products</a>
      <a class="btn btn--ghost" href="/contact">Contact</a>
    </div>
  </div>
</section>

<section class="about-section">
  <div class="about-container">
    <div class="about-split">
      <div class="card about-card">
        <h2>What we’re building</h2>
        <p class="about-text">
          A modern webshop where users can browse clothing products, create accounts, and place orders.
          The visual identity is inspired by African textiles and warm earth tones, aiming for a bold but polished look.
        </p>
        <div class="about-pills">
          <span class="pill pill--primary">Webshop</span>
          <span class="pill">Custom Clothing</span>
          <span class="pill">Afro-inspired UI</span>
        </div>
      </div>

      <div class="card about-card">
        <h2>Why it exists</h2>
        <p class="about-text">
          This application is developed as an individual Web Development project using PHP and MVC, combining
          course concepts with original problem-solving and research.
        </p>
        <div class="about-pills">
          <span class="pill">MVC</span>
          <span class="pill">Security</span>
          <span class="pill">User Experience</span>
        </div>
      </div>
    </div>

    <div class="about-grid grid grid--3 mt-4">
      <article class="card reveal">
        <h3>Design with identity 🎨</h3>
        <p class="about-text">
          The UI uses a consistent palette, gradients, and typography to create a distinctive experience
          that feels warm, modern, and confident.
        </p>
      </article>

      <article class="card reveal">
        <h3>Built with structure 🧱</h3>
        <p class="about-text">
          The application follows MVC patterns with routing, repositories/services, and a clean separation
          between logic and views.
        </p>
      </article>

      <article class="card reveal">
        <h3>Security-first mindset 🛡️</h3>
        <p class="about-text">
          Authentication and authorization protect routes, while server-side validation and safe database
          access practices help reduce common attack risks.
        </p>
      </article>
    </div>

    <div class="card about-highlight mt-4 reveal">
      <div class="about-highlight__top">
        <h2>How it’s built</h2>
        <span class="badge">MVC + JSON + JS</span>
      </div>

      <div class="about-highlight__list">
        <div class="about-feature">
          <div class="about-feature__icon">🧭</div>
          <div>
            <h4 class="about-feature__title">Routing + Views</h4>
            <p class="about-text">Clean routes mapped to controllers and views, keeping pages predictable and maintainable.</p>
          </div>
        </div>

        <div class="about-feature">
          <div class="about-feature__icon">🗃️</div>
          <div>
            <h4 class="about-feature__title">Database-backed features</h4>
            <p class="about-text">Products, orders, users, and admin management are powered by related tables.</p>
          </div>
        </div>

        <div class="about-feature">
          <div class="about-feature__icon">⚡</div>
          <div>
            <h4 class="about-feature__title">JavaScript UX</h4>
            <p class="about-text">
              JavaScript is used to improve usability (navigation behavior, dynamic interactions, and API-driven updates where applicable).
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="about-cta mt-4">
      <div class="card about-cta__card reveal">
        <h2>Explore the collection</h2>
        <p class="about-text">
          Discover products, save favourites, and experience a webshop designed to feel smooth on mobile and desktop.
        </p>
        <a class="btn btn--secondary" href="/products">Go to Products</a>
      </div>
    </div>
  </div>
</section>
