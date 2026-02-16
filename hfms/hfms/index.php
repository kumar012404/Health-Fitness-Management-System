<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Health and Fitness Management System - Track your health, manage fitness, and achieve your wellness goals.">
    <title>HFMS - Health & Fitness Management System</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Landing Page Specific Styles */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            color: #fff;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-text h1 span {
            background: linear-gradient(to right, #ffd700, #ffaa00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-text p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-hero {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-hero-primary {
            background: #fff;
            color: #667eea;
        }

        .btn-hero-outline {
            background: transparent;
            border: 2px solid #fff;
            color: #fff;
        }

        .hero-image {
            text-align: center;
        }

        .hero-image img {
            max-width: 100%;
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.2));
        }

        .features {
            padding: 6rem 0;
            background: #f7fafc;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: var(--gray-600);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--gray-600);
        }

        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            text-align: center;
        }

        .cta-section h2 {
            color: #fff;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .landing-nav {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 1.5rem 0;
            z-index: 100;
        }

        .landing-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .landing-nav .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .landing-nav .nav-links {
            display: flex;
            gap: 2rem;
        }

        .landing-nav .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            transition: color 0.3s;
        }

        .landing-nav .nav-links a:hover {
            color: #fff;
        }

        footer {
            background: #1a202c;
            color: #fff;
            padding: 3rem 0;
            text-align: center;
        }

        footer p {
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 992px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text p {
                margin: 0 auto 2rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-image {
                display: none;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 2.5rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .landing-nav .nav-links {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-heartbeat"></i>
                HFMS
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="login.php">Login</a>
                <a href="login.php?role=admin" class="btn btn-hero-outline btn-sm" style="border-width: 1px; padding: 0.5rem 1rem; font-size: 0.9rem; margin-right: 0.5rem;">Admin Login</a>
                <a href="register.php" class="btn btn-hero-outline btn-sm">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Take Control of Your <span>Health & Fitness</span></h1>
                    <p>Track your health metrics, manage fitness activities, get personalized recommendations, and
                        achieve your wellness goals with our comprehensive health management system.</p>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn btn-hero btn-hero-primary">Get Started Free</a>
                        <a href="#features" class="btn btn-hero btn-hero-outline">Learn More</a>
                    </div>
                </div>
                <div class="hero-image">
                    <svg width="400" height="400" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="200" cy="200" r="180" fill="white" fill-opacity="0.1" />
                        <circle cx="200" cy="200" r="140" fill="white" fill-opacity="0.15" />
                        <path
                            d="M200 80C200 80 280 140 280 200C280 260 200 320 200 320C200 320 120 260 120 200C120 140 200 80 200 80Z"
                            fill="#FF6B6B" />
                        <path d="M200 120L215 160H260L225 185L240 230L200 200L160 230L175 185L140 160H185L200 120Z"
                            fill="#FFD93D" />
                        <circle cx="150" cy="280" r="30" fill="#6BCB77" />
                        <circle cx="250" cy="280" r="30" fill="#4D96FF" />
                        <text x="140" y="285" fill="white" font-size="20" font-weight="bold">💪</text>
                        <text x="240" y="285" fill="white" font-size="20" font-weight="bold">🏃</text>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features for Your Health Journey</h2>
                <p>Everything you need to track, manage, and improve your health and fitness in one place.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" style="background: rgba(102,126,234,0.1); color: #667eea;">
                        <i class="fas fa-weight"></i>
                    </div>
                    <h3>BMI Calculator</h3>
                    <p>Calculate your Body Mass Index instantly and understand your health category with personalized
                        advice.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: rgba(72,187,120,0.1); color: #48bb78;">
                        <i class="fas fa-running"></i>
                    </div>
                    <h3>Activity Tracking</h3>
                    <p>Log your daily steps, exercises, and activities. Monitor your progress towards fitness goals.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: rgba(237,100,166,0.1); color: #ed64a6;">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h3>Water Intake</h3>
                    <p>Track your daily water consumption and stay hydrated with visual progress indicators.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: rgba(236,201,75,0.1); color: #ecc94b;">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Diet Recommendations</h3>
                    <p>Get personalized diet plans and nutritional advice based on your health profile and goals.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: rgba(66,153,225,0.1); color: #4299e1;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Reports</h3>
                    <p>Visualize your health journey with interactive charts and detailed weekly/monthly reports.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: rgba(245,101,101,0.1); color: #f56565;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Smart Reminders</h3>
                    <p>Set reminders for exercises, water intake, medications, and meals to stay on track.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="about">
        <div class="container">
            <h2>Start Your Health Journey Today</h2>
            <p>Join thousands of users who are taking control of their health and fitness.</p>
            <a href="register.php" class="btn btn-hero btn-hero-primary">Create Free Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Health & Fitness Management System. All rights reserved.</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Final Year Academic Project</p>
        </div>
    </footer>

    <script>
        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>

</html>