<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CourseMatch - Chart Your Course Among the Stars ✨</title>
    <meta name="description" content="AI-powered course recommendations for Filipino SHS students. Find your perfect college course based on your grades, interests, and personality.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="#how-it-works">How it Works</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php" class="btn btn-stellar btn-sm">Get Started</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Background Effects -->
        <div class="constellation-bg"></div>
        <div class="nebula-blob nebula-blob-1"></div>
        <div class="nebula-blob nebula-blob-2"></div>
        <div class="nebula-blob nebula-blob-3"></div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span>✨</span>
                    Free for All SHS Students
                </div>
                
                <h1>
                    Chart Your Course<br>
                    <span class="gradient-text">Among the Stars</span>
                </h1>
                
                <p class="hero-subtitle">
                    Not sure what to take in college? Our AI analyzes your grades, interests, and personality to recommend the perfect course for your stellar future.
                </p>
                
                <div class="hero-buttons">
                    <a href="signup.php" class="btn btn-stellar btn-lg">
                        🚀 Start Your Journey
                    </a>
                    <a href="#how-it-works" class="btn btn-orbit btn-lg">
                        How It Works
                    </a>
                </div>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="stat-number">2,847</div>
                        <div class="stat-label">Students Helped</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Course Matches</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Accuracy Rate</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <span>Scroll to explore</span>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Your Journey to the <span class="text-gradient">Perfect Course</span></h2>
                <p>Three simple steps to discover which college course will help you reach the stars</p>
            </div>
            
            <div class="steps-grid stagger-children">
                <div class="step-card animate-fade-in-up">
                    <div class="step-number">1</div>
                    <div class="step-icon">📚</div>
                    <h3>Share Your Grades</h3>
                    <p>Enter your SHS subject grades - Math, Science, English, and more. We'll analyze your academic strengths.</p>
                </div>
                
                <div class="step-card animate-fade-in-up">
                    <div class="step-number">2</div>
                    <div class="step-icon">💡</div>
                    <h3>Tell Us Your Interests</h3>
                    <p>What excites you? Technology, arts, business, or cooking? Your passions shape your perfect path.</p>
                </div>
                
                <div class="step-card animate-fade-in-up">
                    <div class="step-number">3</div>
                    <div class="step-icon">🌟</div>
                    <h3>Get Your Match</h3>
                    <p>Our AI creates your unique constellation - a personalized course recommendation with career paths and salary info.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section" style="background: var(--stardust-100);">
        <div class="container">
            <div class="section-header">
                <h2>Why Students <span class="text-gradient">Love</span> CourseMatch</h2>
                <p>Built specifically for students making their biggest decision yet</p>
            </div>
            
            <div class="feature-grid stagger-children">
                <div class="feature-card animate-fade-in-up">
                    <span class="feature-icon">🎯</span>
                    <h3>AI-Powered Accuracy</h3>
                    <p>Machine learning analyzes patterns from thousands of successful students to match you perfectly</p>
                </div>
                
                <div class="feature-card animate-fade-in-up">
                    <span class="feature-icon">💼</span>
                    <h3>Career Insights</h3>
                    <p>See real job opportunities and salary ranges for each recommended course</p>
                </div>
                
                <div class="feature-card animate-fade-in-up">
                    <span class="feature-icon">⚡</span>
                    <h3>Quick Results</h3>
                    <p>Get your personalized recommendations in just 5-7 minutes</p>
                </div>
                
                <div class="feature-card animate-fade-in-up">
                    <span class="feature-icon">🔒</span>
                    <h3>Private & Secure</h3>
                    <p>Your data stays safe and is never shared with third parties</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Your <span class="text-gradient">Unique Constellation</span></h2>
                <p>Every student creates their own star pattern based on their answers</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-9); align-items: center;">
                <div style="padding: var(--space-6);">
                    <h3 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; margin-bottom: var(--space-5); color: var(--space-dark);">
                        Made for Students Like You
                    </h3>
                    <p style="color: var(--stardust-500); line-height: 1.8; margin-bottom: var(--space-4);">
                        We know choosing a college course feels overwhelming. Should you follow your passion? What about job opportunities? Will you even like studying it for 4 years?
                    </p>
                    <p style="color: var(--stardust-500); line-height: 1.8; margin-bottom: var(--space-6);">
                        CourseMatch takes the guesswork out. By combining your grades, interests, and personality traits, we map out which courses align with who you really are - not just what's trending.
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                        <div style="display: flex; align-items: center; gap: var(--space-3); color: var(--stardust-600);">
                            <span style="color: var(--success); font-size: 1.25rem;">✓</span>
                            No judgment, no pressure - explore freely
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--space-3); color: var(--stardust-600);">
                            <span style="color: var(--success); font-size: 1.25rem;">✓</span>
                            Based on real student success stories
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--space-3); color: var(--stardust-600);">
                            <span style="color: var(--success); font-size: 1.25rem;">✓</span>
                            Retake anytime as your goals evolve
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: center;">
                    <!-- Constellation Visual -->
                    <svg width="400" height="400" viewBox="0 0 400 400" style="max-width: 100%;">
                        <!-- Background glow -->
                        <defs>
                            <radialGradient id="glow" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" stop-color="#7C3AED" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#7C3AED" stop-opacity="0"/>
                            </radialGradient>
                            <filter id="blur" x="-50%" y="-50%" width="200%" height="200%">
                                <feGaussianBlur in="SourceGraphic" stdDeviation="3"/>
                            </filter>
                        </defs>
                        <circle cx="200" cy="200" r="180" fill="url(#glow)"/>
                        
                        <!-- Constellation lines -->
                        <g stroke="#7C3AED" stroke-width="1.5" opacity="0.6">
                            <line x1="100" y1="120" x2="180" y2="160"/>
                            <line x1="180" y1="160" x2="200" y2="200"/>
                            <line x1="200" y1="200" x2="280" y2="180"/>
                            <line x1="200" y1="200" x2="160" y2="280"/>
                            <line x1="160" y1="280" x2="240" y2="320"/>
                            <line x1="280" y1="180" x2="320" y2="140"/>
                            <line x1="280" y1="180" x2="300" y2="260"/>
                        </g>
                        
                        <!-- Star dots -->
                        <g fill="#7C3AED">
                            <circle cx="100" cy="120" r="8" filter="url(#blur)"/>
                            <circle cx="100" cy="120" r="4"/>
                            <circle cx="180" cy="160" r="10" filter="url(#blur)"/>
                            <circle cx="180" cy="160" r="5"/>
                            <circle cx="200" cy="200" r="14" filter="url(#blur)" fill="#EC4899"/>
                            <circle cx="200" cy="200" r="7" fill="#EC4899"/>
                            <circle cx="280" cy="180" r="10" filter="url(#blur)"/>
                            <circle cx="280" cy="180" r="5"/>
                            <circle cx="160" cy="280" r="8" filter="url(#blur)"/>
                            <circle cx="160" cy="280" r="4"/>
                            <circle cx="240" cy="320" r="6" filter="url(#blur)" fill="#3B82F6"/>
                            <circle cx="240" cy="320" r="3" fill="#3B82F6"/>
                            <circle cx="320" cy="140" r="6" filter="url(#blur)" fill="#FBBF24"/>
                            <circle cx="320" cy="140" r="3" fill="#FBBF24"/>
                            <circle cx="300" cy="260" r="8" filter="url(#blur)" fill="#06B6D4"/>
                            <circle cx="300" cy="260" r="4" fill="#06B6D4"/>
                        </g>
                        
                        <!-- Labels -->
                        <text x="100" y="100" fill="var(--stardust-500)" font-size="10" text-anchor="middle">Grades</text>
                        <text x="320" y="125" fill="var(--stardust-500)" font-size="10" text-anchor="middle">Interests</text>
                        <text x="300" y="285" fill="var(--stardust-500)" font-size="10" text-anchor="middle">Personality</text>
                        <text x="200" y="225" fill="var(--stardust-400)" font-size="11" text-anchor="middle" font-weight="600">YOUR PATH</text>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Discover Your Stars? 🌟</h2>
                <p>Join 2,847 students who've already charted their course to the future</p>
                <a href="signup.php" class="btn btn-stellar btn-lg">
                    🚀 Start Your Free Assessment
                </a>
                <p class="cta-note">Takes only 5-7 minutes • 100% Free • No signup fees</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section" style="background: var(--stardust-100);"> 
        <div class="container">
            <div class="section-header">
                <h2>Questions? We're Here to <span class="text-gradient">Help</span></h2>
                <p>Reach out anytime - we love hearing from students</p>
            </div>
            
            <div class="contact-grid">
                <div class="contact-card">
                    <span class="contact-icon">📧</span>
                    <h4>Email Us</h4>
                    <p>hello@coursematch.ph</p>
                    <p class="text-muted">We reply within 24 hours</p>
                </div>
                
                <div class="contact-card">
                    <span class="contact-icon">💬</span>
                    <h4>Live Chat</h4>
                    <p>Available Mon-Fri</p>
                    <p class="text-muted">8:00 AM - 5:00 PM</p>
                </div>
                
                <div class="contact-card">
                    <span class="contact-icon">📍</span>
                    <h4>Location</h4>
                    <p>Laguna, Philippines</p>
                    <p class="text-muted">Serving students nationwide</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>✨ CourseMatch</h3>
                    <p>Helping students discover their perfect college course through AI-powered recommendations.</p>
                </div>
                
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Product</h4>
                        <a href="#how-it-works">How it Works</a>
                        <a href="#features">Features</a>
                        <a href="signup.php">Get Started</a>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Support</h4>
                        <a href="#contact">Contact</a>
                        <a href="#">FAQ</a>
                        <a href="#">Help Center</a>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Legal</h4>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© 2025 CourseMatch. </p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.getElementById('nav-menu');

        mobileToggle.addEventListener('click', () => {
            mobileToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        // Close mobile menu
                        mobileToggle.classList.remove('active');
                        navMenu.classList.remove('active');
                    }
                }
            });
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
            observer.observe(el);
        });

        // Stats counter animation
        const statNumbers = document.querySelectorAll('.stat-number');
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const text = target.textContent;
                    const isPercentage = text.includes('%');
                    const isPlus = text.includes('+');
                    const number = parseInt(text.replace(/[^0-9]/g, ''));
                    
                    let current = 0;
                    const increment = number / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= number) {
                            current = number;
                            clearInterval(timer);
                        }
                        target.textContent = Math.floor(current) + (isPercentage ? '%' : '') + (isPlus ? '+' : '');
                    }, 30);
                    
                    statsObserver.unobserve(target);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(stat => statsObserver.observe(stat));
    </script>
</body>
</html>
