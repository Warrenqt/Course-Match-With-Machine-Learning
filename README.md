# ✨ CourseMatch: AI-Powered Course Recommendation System

Find your perfect college course. Discover your path, instantly.

CourseMatch is an intelligent web application designed to help Senior High School students in the Philippines discover the ideal college course that matches their academic performance, interests, and personality traits through advanced machine learning algorithms.

## 💡 The Problem & The Solution

Choosing a college course is one of the most critical decisions a student makes. With hundreds of courses available and countless factors to consider—academic strengths, personal interests, career prospects, and personality fit—students often feel overwhelmed and uncertain about their future path.

CourseMatch solves this by instantly analyzing a student's SHS grades, interests, and personality traits to generate personalized course recommendations. Using machine learning trained on real student success data, the system delivers accurate, data-driven suggestions that help students make informed decisions about their college education, significantly reducing uncertainty and guiding them toward courses where they're most likely to succeed.

## 🚀 Try It Out

| Feature | Link |
|---------|------|
| 🌐 Web Application | https://course-match.infinityfree.me/public/index.php |
| 📊 Project Presentation | https://www.canva.com/design/DAG7SpeRHXg/YdyV68tRiq8ig4BDu8Ihjg/edit |
| ▶️ Video Presentation | https://www.youtube.com/watch?v=BoyjRZdfESQ |

## 🛠️ Technology Stack

CourseMatch is built using a robust combination of web technologies and advanced machine learning libraries.

### 💻 Web Application

The front-end and back-end web application is powered by the following technology:

**Primary Framework**: PHP (Native PHP with PDO for database operations)

**Frontend Technologies**:
- HTML5 & CSS3 (Custom Stellar Design System)
- JavaScript (Vanilla JS for interactivity)
- Responsive Design (Mobile-first approach)

**Backend Technologies**:
- PHP 8.0+ (Server-side logic and API)
- MySQL 5.7+ (Database management)
- PDO (Secure database connections with prepared statements)

### 🤖 Machine Learning & Libraries

The core AI functionality is driven by the following libraries:

**PHP / Packagist**:
- `php-ai/php-ml` (v0.10.0): Machine learning library for PHP, specifically:
  - K-Nearest Neighbors (KNN) Classifier
  - Data normalization and preprocessing
  - Model training and prediction

**Machine Learning Model**:
- **Algorithm**: K-Nearest Neighbors (KNN) Classifier
- **Training Data**: 500+ student records with course outcomes
- **Features Analyzed**:
  - Interest categories (Problem Solving, Art, Technology, Business, Culinary)
  - Personality traits (Analytical, Empathetic, Adventurous, Creative, Independent)
  - 7 SHS Subject Grades (Mathematics, Science, English, Filipino, MAPEH, TLE, Araling Panlipunan)
- **Output**: Top 3 course recommendations with match scores

**Supported Courses** (15+):
- Computer Science, Information Technology, Computer Engineering
- Engineering, Business Administration, Accounting, Marketing
- Fine Arts, Multimedia Arts, Culinary Arts
- Hotel & Restaurant Management, Nutrition, Nursing
- Education, Communication Arts, Architecture

## 🔐 Security Features

CourseMatch implements industry-standard security practices:

- **Argon2ID Password Hashing**: Industry-standard password security
- **Prepared Statements**: SQL injection prevention
- **CSRF Protection**: Cross-site request forgery protection
- **Rate Limiting**: Brute force attack prevention (5 attempts per 15 minutes)
- **Session Security**: Secure session management with HTTPOnly cookies
- **Input Validation**: Comprehensive client and server-side validation
- **XSS Protection**: Proper input sanitization and output escaping

## 📁 Project Structure

```
Course_Reco/
├── ml/                          # Machine Learning Components
│   ├── data/                    # Training datasets
│   ├── models/                  # Trained ML models
│   ├── predictor.php            # Prediction engine
│   └── train_model.php          # Model training script
├── assets/                      # Static assets (CSS, JS, images)
├── includes/                    # Shared PHP components
├── admin/                       # Admin panel
├── config.php                   # Database & app configuration
├── functions.php                # Helper functions
├── index.php                    # Homepage
├── login.php                    # User authentication
├── signup.php                   # User registration
├── dashboard.php                # User dashboard
├── assessment.php               # Course assessment form
├── results.php                  # Recommendation results
├── profile.php                  # User profile management
└── database_schema.sql          # Database structure
```

## 🚀 Key Features

- **AI-Powered Recommendations**: Machine learning analyzes patterns from successful students
- **Comprehensive Assessment**: Evaluates grades, interests, and personality traits
- **Career Insights**: Shows job opportunities and salary ranges for each course
- **User Dashboard**: Track assessment history and view past recommendations
- **Leaderboard**: See how your scores compare with other students
- **Secure Authentication**: Modern security standards with account protection
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices


## 👨‍💻 Project Team

The CourseMatch project was developed by a dedicated team of developers:

| Role | Name |
|------|------|
| Lead Developer / Project Manager | Marvin L. Naje |
| Developer / Database Administrator  | Warren A. Panergayo |
| Documentation/ Backup Developer | Lenard L. Tierra |

## 🤝 Contribution & Support

If you have any suggestions or would like to contribute to the project, please feel free to:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request


## 🔄 Future Enhancements

- Enhanced ML algorithms for better accuracy
- Email verification system
- Password reset functionality
- Mobile app companion
- Integration with university APIs
- Advanced career counseling features
- Progress tracking and analytics
- Multi-language support

---

**Made for students seeking their perfect college course**

