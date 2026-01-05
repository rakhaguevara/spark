<section id="home" class="hero">
  <div class="hero-container">

    <!-- LEFT CONTENT -->
    <div class="hero-text">
      <h1 id="hero-typing">
        SPARK HERE<br>
        RESERVE YOUR PARKING<br>
        EFFORTLESSLY.
      </h1>

      <p>
        Temukan dan reservasi tempat parkir secara real-time dengan mudah.
        SPARK menghadirkan pengalaman parkir yang cepat, efisien, dan bebas stres
        di era digital.
      </p>

      <div class="hero-actions">
        <a href="#" class="btn btn-primary spark-btn-primary">Book Now!</a>
        <a href="<?= BASEURL ?>/login.php" class="btn spark-btn-outline">Log In</a>
      </div>
    </div>

    <!-- RIGHT IMAGE -->
    <div class="hero-image">
      <div class="hero-image-mask"></div>
      <img src="<?= BASEURL ?>/assets/img/content-1.png" alt="Parking">
    </div>

  </div>
</section>


<section id="service" class="spark-works">
    <div class="spark-works-container">

  <!-- HEADER -->
  <header class="spark-works-header">
    <span class="spark-works-label">How It Works</span>
    <h2>Park Smarter with SPARK</h2>
    <p>
      Tiga langkah sederhana untuk menemukan, memesan,
      dan mengelola parkir tanpa stres.
    </p>
  </header>

  <!-- STEPS -->
  <div class="spark-works-steps">

    <article class="spark-work-card">
      <span class="step">01</span>
      <div class="icon">
        <img src="<?= BASEURL ?>/assets/img/maps-for-feature.avif" alt="Find Parking">
      </div>
      <h4>Find Parking</h4>
      <p>
        Temukan area parkir terdekat secara real-time
        melalui peta interaktif SPARK.
      </p>
    </article>

    <article class="spark-work-card">
      <span class="step">02</span>
      <div class="icon">
        <img src="<?= BASEURL ?>/assets/img/qrcode-for-feature.avif" alt="Start Parking">
      </div>
      <h4>Start Parking</h4>
      <p>
        Pilih durasi, kendaraan, dan metode pembayaran,
        lalu mulai parkir tanpa ribet.
      </p>
    </article>

    <article class="spark-work-card">
      <span class="step">03</span>
      <div class="icon">
        <img src="<?= BASEURL ?>/assets/img/park-for-feature.avif" alt="Stop Parking">
      </div>
      <h4>Stop or Extend</h4>
      <p>
        Hentikan atau perpanjang parkir langsung dari aplikasi,
        bayar sesuai waktu penggunaan.
      </p>
    </article>

  </div>
    </div>
</section>

<section id="reserve">
  <?php require_once __DIR__ . '/../includes/bookpark.php'; ?>
</section>
<?php require_once __DIR__ . '/../includes/explore.php'; ?>

<section class="features">

  <!-- HEADER -->
  <div class="features-header">
    <span class="spark-works-label">WHY SPARK</span>
    <h2>Designed for Stress-Free Parking</h2>
    <p>
      SPARK menghadirkan solusi parkir modern yang transparan,
      aman, dan efisien untuk menunjang mobilitas Anda di mana saja.
    </p>
  </div>

  <!-- GRID -->
  <div class="features-container">

    <!-- CARD 1 -->
    <div class="feature-card">
      <div class="feature-icon">💰</div>
      <h3>Transparansi Harga Bayar</h3>
      <p>
        Tidak perlu khawatir dengan biaya parkir tersembunyi.
        Semua harga ditampilkan secara jelas sebelum Anda memesan.
      </p>

      <ul class="feature-list">
        <li>Perhitungan harga tetap dan transparan</li>
        <li>Penyesuaian tarif berdasarkan waktu masuk</li>
        <li>Tidak ada denda parkir mendadak</li>
        <li>Bebas mengubah jadwal parkir kapan saja</li>
        <li>Riwayat transaksi dapat diakses pengguna</li>
      </ul>
    </div>

    <!-- CARD 2 -->
    <div class="feature-card">
      <div class="feature-icon">⚡</div>
      <h3>Akses Mudah & Cepat</h3>
      <p>
        Temukan dan pesan tempat parkir terdekat hanya dalam
        beberapa langkah melalui aplikasi SPARK.
      </p>

      <ul class="feature-list">
        <li>Peta interaktif dengan navigasi real-time</li>
        <li>Informasi ketersediaan parkir secara langsung</li>
        <li>Proses reservasi instan tanpa antre</li>
        <li>Notifikasi status parkir otomatis</li>
        <li>Integrasi pembayaran digital yang aman</li>
      </ul>
    </div>

    <!-- CARD 3 -->
    <div class="feature-card">
      <div class="feature-icon">🔒</div>
      <h3>Keamanan Terjamin</h3>
      <p>
        Sistem keamanan modern dirancang untuk memastikan kendaraan
        Anda tetap aman selama berada di area parkir.
      </p>

      <ul class="feature-list">
        <li>Check-in parkir menggunakan QR Code</li>
        <li>Pemantauan area parkir secara berkala</li>
        <li>Riwayat parkir tersimpan di akun pengguna</li>
        <li>Data pengguna terenkripsi end-to-end</li>
        <li>Kontrol akses parkir berbasis sistem</li>
      </ul>
    </div>

  </div>
</section>


<?php require_once __DIR__ . '/../includes/logo-clients.php'; ?>


<section class="testimonials">
    <div class="testimonials-container">

        <!-- HEADER -->
        <div class="testimonials-header">
            <div>
                <h2>Why Say Our Client</h2>
                <p>
                    Tidak perlu berkeliling mencari parkir. SPARK memandu Anda
                    menemukan ruang parkir terdekat dan memastikan tempat tersedia
                    sebelum Anda tiba.
                </p>
            </div>

            <a href="#" class="spark-btn-outline small">View All</a>
        </div>

        <!-- LIST -->
        <div class="testimonial-list">

            <!-- CARD -->
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>

                <p class="testimonial-text">
                    Aku sudah menggunakan SPARK dan jujur ini sangat memudahkan
                    aku dalam berkeliling kota. Tidak perlu takut parkir dan biaya
                    yang mahal lagi.
                </p>

                <div class="testimonial-footer">
                    <div class="user">
                        <img src="<?= BASEURL; ?>/assets/img/user-1.png" alt="User">
                        <div>
                            <strong>Prabowo Subiyanti</strong>
                            <span>PARKSTER</span>
                        </div>
                    </div>

                    <span class="date">Posted 16/06/2025</span>
                </div>
            </div>

            <!-- DUPLIKASI CARD -->
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>

                <p class="testimonial-text">
                    SPARK benar-benar membantu. Parkir jadi lebih cepat, aman,
                    dan transparan. Sangat direkomendasikan!
                </p>

                <div class="testimonial-footer">
                    <div class="user">
                        <img src="<?= BASEURL; ?>/assets/img/user-2.png" alt="User">
                        <div>
                            <strong>Siti Rahmawati</strong>
                            <span>PARKSTER</span>
                        </div>
                    </div>

                    <span class="date">Posted 12/06/2025</span>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="stars">★★★★★</div>

                <p class="testimonial-text">
                    Pengalaman parkir terbaik yang pernah saya gunakan. UI bersih,
                    sistem jelas, dan sangat membantu saat bepergian.
                </p>

                <div class="testimonial-footer">
                    <div class="user">
                        <img src="<?= BASEURL; ?>/assets/img/user-3.png" alt="User">
                        <div>
                            <strong>Ahmad Fauzi</strong>
                            <span>PARKSTER</span>
                        </div>
                    </div>

                    <span class="date">Posted 10/06/2025</span>
                </div>
            </div>
        </div>
    </div>
</section>


<section id="contact" class="spark2-contact-section">
    <div class="spark2-contact-container">

        <div class="spark2-contact-header">
            <span class="spark2-contact-label">Contact Us</span>
            <h2>Get in Touch with Our Team</h2>
            <p>
                Kami siap membantu pertanyaan Anda dan menemukan
                solusi parkir terbaik bersama SPARK.
            </p>
        </div>

        <div class="spark2-contact-content">

            <!-- FORM -->
            <form class="spark2-contact-form" method="POST" action="<?= BASEURL ?>/contact_process.php">

                <h4>Let’s Talk About Your Project</h4>

                <div class="spark2-form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>

                <div class="spark2-form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="spark2-form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" required>
                </div>

                <div class="spark2-form-group">
                    <label>Message</label>
                    <textarea name="message" rows="4" required></textarea>
                </div>

                <button type="submit" class="spark2-contact-submit">
                    Send Message
                </button>
            </form>

            <!-- INFO -->
            <div class="spark2-contact-side">

      <div class="spark2-contact-box">
        <h4>Prefer a Direct Approach?</h4>
        <ul>
          <li>📞 +62 823-4567-8901</li>
          <li>📧 contact@sparkparking.id</li>
          <li>🕘 Monday – Friday, 9 AM – 6 PM (GMT)</li>
        </ul>
      </div>

      <div class="spark2-contact-box">
        <h4>Visit Our Office</h4>
        <p>Rt002 Rw005, Jl. Candi Tiga, Candi, Sardonoharjo, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581</p>

        <div class="spark2-map-wrapper">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.5!2d110.4!3d-7.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwNDInMDAuMCJTIDExMMKwMjQnMDAuMCJF!5e0!3m2!1sen!2sid!4v1234567890"
            width="100%"
            height="200"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>

        <a href="#" class="spark2-map-btn">
          Get a Direction →
        </a>
      </div>

    </div>

        </div>
    </div>
</section>


 


