<?php
/**
 * Course Configuration
 * Defines all 12 courses with detailed information for the recommendation system
 */

// Interest Code Mapping
define('INTERESTS', [
    1 => 'Problem Solving / Logical Thinking',
    2 => 'Art / Creativity',
    3 => 'Technology',
    4 => 'Business',
    5 => 'Cooking / Culinary Skills'
]);

// Personality Code Mapping
define('PERSONALITIES', [
    1 => 'Analytical / Logical',
    2 => 'Empathetic / Social',
    3 => 'Adventurous / Risk-Taker',
    4 => 'Creative / Innovative',
    5 => 'Independent / Self-Starter'
]);

// Complete Course Information
define('COURSES', [
    'Computer Science' => [
        'code' => 'BSCS',
        'full_name' => 'Bachelor of Science in Computer Science',
        'description' => 'Study the theoretical foundations of computing, algorithms, programming languages, and software development. Learn to design and build complex software systems.',
        'duration' => '4 years',
        'universities' => ['University of the Philippines', 'Ateneo de Manila', 'De La Salle University', 'UST'],
        'careers' => [
            ['title' => 'Software Engineer', 'salary' => '₱40,000 - ₱120,000/month', 'demand' => 'Very High', 'description' => 'Design, develop, and maintain software applications and systems using programming languages and development tools.'],
            ['title' => 'Data Scientist', 'salary' => '₱50,000 - ₱150,000/month', 'demand' => 'Very High', 'description' => 'Analyze complex data sets to find patterns, build predictive models, and help companies make data-driven decisions.'],
            ['title' => 'Full Stack Developer', 'salary' => '₱35,000 - ₱100,000/month', 'demand' => 'High', 'description' => 'Build complete web applications handling both frontend interfaces and backend server logic.'],
            ['title' => 'AI/ML Engineer', 'salary' => '₱60,000 - ₱180,000/month', 'demand' => 'Very High', 'description' => 'Develop artificial intelligence and machine learning models to automate tasks and create intelligent systems.']
        ],
        'skills' => ['Programming', 'Algorithms', 'Problem Solving', 'Mathematics', 'Data Structures'],
        'icon' => '💻'
    ],

    'Information Technology' => [
        'code' => 'BSIT',
        'full_name' => 'Bachelor of Science in Information Technology',
        'description' => 'Focus on practical applications of technology, network administration, database management, and IT infrastructure. Learn to implement and maintain technology solutions.',
        'duration' => '4 years',
        'universities' => ['PUP', 'TIP', 'FEU Institute of Technology', 'Mapua University'],
        'careers' => [
            ['title' => 'IT Specialist', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'High', 'description' => 'Provide technical support, troubleshoot issues, and maintain computer systems and networks for organizations.'],
            ['title' => 'Network Administrator', 'salary' => '₱35,000 - ₱90,000/month', 'demand' => 'High', 'description' => 'Manage and maintain computer networks, ensuring secure and reliable connectivity across the organization.'],
            ['title' => 'Systems Analyst', 'salary' => '₱40,000 - ₱100,000/month', 'demand' => 'High', 'description' => 'Analyze business requirements and design IT solutions to improve efficiency and solve organizational problems.'],
            ['title' => 'Database Administrator', 'salary' => '₱45,000 - ₱110,000/month', 'demand' => 'Medium', 'description' => 'Manage databases, ensure data security, optimize performance, and implement backup strategies.']
        ],
        'skills' => ['Networking', 'Database Management', 'System Administration', 'Troubleshooting'],
        'icon' => '🖥️'
    ],

    'Engineering' => [
        'code' => 'BSE',
        'full_name' => 'Bachelor of Science in Engineering',
        'description' => 'Apply scientific and mathematical principles to design, build, and maintain structures, machines, and systems. Specializations include Civil, Mechanical, and Electrical Engineering.',
        'duration' => '5 years',
        'universities' => ['UP Diliman', 'Mapua University', 'DLSU', 'UST'],
        'careers' => [
            ['title' => 'Civil Engineer', 'salary' => '₱30,000 - ₱100,000/month', 'demand' => 'High', 'description' => 'Design and oversee construction of infrastructure like roads, bridges, buildings, and water systems.'],
            ['title' => 'Mechanical Engineer', 'salary' => '₱35,000 - ₱90,000/month', 'demand' => 'High', 'description' => 'Design, develop, and test mechanical devices, engines, machines, and thermal systems.'],
            ['title' => 'Electrical Engineer', 'salary' => '₱35,000 - ₱95,000/month', 'demand' => 'High', 'description' => 'Design and develop electrical systems, from power generation to electronics and communication systems.'],
            ['title' => 'Project Manager', 'salary' => '₱50,000 - ₱150,000/month', 'demand' => 'Medium', 'description' => 'Lead engineering teams, manage project timelines, budgets, and ensure successful project delivery.']
        ],
        'skills' => ['Mathematics', 'Physics', 'Problem Solving', 'Technical Drawing', 'Project Management'],
        'icon' => '⚙️'
    ],

    'Business Administration' => [
        'code' => 'BSBA',
        'full_name' => 'Bachelor of Science in Business Administration',
        'description' => 'Learn the fundamentals of business management, marketing, finance, and entrepreneurship. Develop leadership skills and business acumen for the corporate world.',
        'duration' => '4 years',
        'universities' => ['Ateneo de Manila', 'DLSU', 'UP Diliman', 'San Beda University'],
        'careers' => [
            ['title' => 'Business Analyst', 'salary' => '₱35,000 - ₱90,000/month', 'demand' => 'High', 'description' => 'Analyze business processes, identify improvements, and bridge the gap between business needs and technology solutions.'],
            ['title' => 'Marketing Manager', 'salary' => '₱40,000 - ₱120,000/month', 'demand' => 'High', 'description' => 'Develop marketing strategies, manage campaigns, and drive brand awareness and customer acquisition.'],
            ['title' => 'HR Manager', 'salary' => '₱45,000 - ₱100,000/month', 'demand' => 'Medium', 'description' => 'Oversee recruitment, employee relations, training, and ensure a positive workplace culture.'],
            ['title' => 'Entrepreneur', 'salary' => 'Variable', 'demand' => 'High', 'description' => 'Start and run your own business, creating products or services that solve problems in the market.']
        ],
        'skills' => ['Leadership', 'Communication', 'Financial Analysis', 'Marketing', 'Strategic Planning'],
        'icon' => '💼'
    ],

    'Accounting' => [
        'code' => 'BSA',
        'full_name' => 'Bachelor of Science in Accountancy',
        'description' => 'Master financial reporting, auditing, taxation, and business law. Prepare for the CPA board exam and become a trusted financial professional.',
        'duration' => '4 years',
        'universities' => ['UST', 'San Beda University', 'UP Diliman', 'DLSU'],
        'careers' => [
            ['title' => 'Certified Public Accountant', 'salary' => '₱35,000 - ₱100,000/month', 'demand' => 'High', 'description' => 'Prepare financial statements, conduct audits, and ensure compliance with accounting standards and regulations.'],
            ['title' => 'Financial Analyst', 'salary' => '₱40,000 - ₱120,000/month', 'demand' => 'High', 'description' => 'Analyze financial data, create forecasts, and provide insights to guide investment and business decisions.'],
            ['title' => 'Auditor', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'Medium', 'description' => 'Examine financial records to verify accuracy, detect fraud, and ensure regulatory compliance.'],
            ['title' => 'Tax Consultant', 'salary' => '₱45,000 - ₱130,000/month', 'demand' => 'Medium', 'description' => 'Advise individuals and businesses on tax strategies, compliance, and help minimize tax liabilities legally.']
        ],
        'skills' => ['Financial Analysis', 'Attention to Detail', 'Mathematics', 'Tax Law', 'Auditing'],
        'icon' => '📊'
    ],

    'Fine Arts' => [
        'code' => 'BFA',
        'full_name' => 'Bachelor of Fine Arts',
        'description' => 'Develop artistic skills in painting, sculpture, and visual arts. Express creativity while learning art history, theory, and contemporary practices.',
        'duration' => '4 years',
        'universities' => ['UP Diliman', 'UST', 'PUP', 'Philippine Women\'s University'],
        'careers' => [
            ['title' => 'Visual Artist', 'salary' => '₱20,000 - ₱80,000/month', 'demand' => 'Medium', 'description' => 'Create original artworks using various mediums like painting, drawing, sculpture, or mixed media.'],
            ['title' => 'Art Director', 'salary' => '₱40,000 - ₱120,000/month', 'demand' => 'Medium', 'description' => 'Lead creative teams and oversee the visual style and imagery in advertisements, films, or publications.'],
            ['title' => 'Gallery Curator', 'salary' => '₱30,000 - ₱70,000/month', 'demand' => 'Low', 'description' => 'Manage art collections, organize exhibitions, and research artworks for museums and galleries.'],
            ['title' => 'Art Teacher', 'salary' => '₱25,000 - ₱50,000/month', 'demand' => 'Medium', 'description' => 'Teach art techniques, history, and theory to students at various educational levels.']
        ],
        'skills' => ['Drawing', 'Painting', 'Creativity', 'Art History', 'Visual Communication'],
        'icon' => '🎨'
    ],

    'Multimedia Arts / Design' => [
        'code' => 'BSMMA',
        'full_name' => 'Bachelor of Science in Multimedia Arts',
        'description' => 'Combine art and technology to create digital content, animations, graphics, and interactive media. Learn industry-standard design software and techniques.',
        'duration' => '4 years',
        'universities' => ['DLSU', 'Asia Pacific College', 'iAcademy', 'CIIT'],
        'careers' => [
            ['title' => 'Graphic Designer', 'salary' => '₱25,000 - ₱70,000/month', 'demand' => 'High', 'description' => 'Create visual content for branding, marketing, websites, and print materials using design software.'],
            ['title' => 'UI/UX Designer', 'salary' => '₱35,000 - ₱100,000/month', 'demand' => 'Very High', 'description' => 'Design user-friendly interfaces and experiences for apps and websites, focusing on usability and aesthetics.'],
            ['title' => 'Animator', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'High', 'description' => 'Create moving images and visual effects for films, games, advertisements, and digital media.'],
            ['title' => 'Video Editor', 'salary' => '₱25,000 - ₱60,000/month', 'demand' => 'High', 'description' => 'Edit and assemble video footage, add effects, and create polished content for various platforms.']
        ],
        'skills' => ['Adobe Creative Suite', 'Animation', 'UI/UX Design', 'Video Editing', 'Creativity'],
        'icon' => '🎬'
    ],

    'Culinary Arts' => [
        'code' => 'BSCA',
        'full_name' => 'Bachelor of Science in Culinary Arts',
        'description' => 'Master the art and science of cooking, baking, and food service management. Learn from professional chefs and gain hands-on kitchen experience.',
        'duration' => '4 years',
        'universities' => ['Enderun Colleges', 'ISCAHM', 'Benilde', 'Global Culinary Institute'],
        'careers' => [
            ['title' => 'Executive Chef', 'salary' => '₱40,000 - ₱150,000/month', 'demand' => 'High', 'description' => 'Lead kitchen operations, design menus, manage staff, and ensure food quality in restaurants or hotels.'],
            ['title' => 'Pastry Chef', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'Medium', 'description' => 'Specialize in creating desserts, breads, pastries, and baked goods with artistic presentation.'],
            ['title' => 'Restaurant Manager', 'salary' => '₱35,000 - ₱90,000/month', 'demand' => 'High', 'description' => 'Oversee daily restaurant operations, manage staff, handle customer service, and ensure profitability.'],
            ['title' => 'Food Entrepreneur', 'salary' => 'Variable', 'demand' => 'High', 'description' => 'Start your own food business, from food trucks and cafes to catering services and food products.']
        ],
        'skills' => ['Cooking Techniques', 'Food Safety', 'Menu Planning', 'Kitchen Management', 'Creativity'],
        'icon' => '👨‍🍳'
    ],

    'Hotel & Restaurant Management' => [
        'code' => 'BSHRM',
        'full_name' => 'Bachelor of Science in Hotel and Restaurant Management',
        'description' => 'Learn hospitality operations, hotel management, food service, and tourism. Develop skills for the thriving hospitality industry.',
        'duration' => '4 years',
        'universities' => ['Benilde', 'Lyceum of the Philippines', 'UST', 'CEU'],
        'careers' => [
            ['title' => 'Hotel Manager', 'salary' => '₱40,000 - ₱120,000/month', 'demand' => 'High', 'description' => 'Oversee all aspects of hotel operations, from guest services to housekeeping, ensuring exceptional experiences.'],
            ['title' => 'Restaurant Manager', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'High', 'description' => 'Manage restaurant operations, staff scheduling, inventory, and ensure customer satisfaction.'],
            ['title' => 'Events Coordinator', 'salary' => '₱25,000 - ₱60,000/month', 'demand' => 'Medium', 'description' => 'Plan and execute events like weddings, conferences, and parties, coordinating vendors and logistics.'],
            ['title' => 'Front Office Manager', 'salary' => '₱35,000 - ₱70,000/month', 'demand' => 'Medium', 'description' => 'Manage hotel reception, reservations, and guest relations, ensuring smooth check-in/check-out processes.']
        ],
        'skills' => ['Customer Service', 'Operations Management', 'Communication', 'Problem Solving', 'Leadership'],
        'icon' => '🏨'
    ],

    'Nursing' => [
        'code' => 'BSN',
        'full_name' => 'Bachelor of Science in Nursing',
        'description' => 'Prepare for a rewarding career in healthcare. Learn patient care, medical procedures, and health sciences to become a registered nurse.',
        'duration' => '4 years',
        'universities' => ['UP Manila', 'UST', 'San Beda University', 'Ateneo de Manila'],
        'careers' => [
            ['title' => 'Registered Nurse', 'salary' => '₱25,000 - ₱60,000/month (PH) / $4,000-8,000 (abroad)', 'demand' => 'Very High', 'description' => 'Provide direct patient care, administer medications, and work alongside doctors in hospitals and clinics.'],
            ['title' => 'Clinical Nurse Specialist', 'salary' => '₱40,000 - ₱80,000/month', 'demand' => 'High', 'description' => 'Expert nurse who focuses on specific areas like oncology, pediatrics, or critical care, providing advanced care.'],
            ['title' => 'Nurse Educator', 'salary' => '₱35,000 - ₱70,000/month', 'demand' => 'Medium', 'description' => 'Train and educate nursing students and healthcare staff in clinical skills and best practices.'],
            ['title' => 'Healthcare Administrator', 'salary' => '₱50,000 - ₱100,000/month', 'demand' => 'Medium', 'description' => 'Manage healthcare facilities, coordinate services, and ensure efficient hospital operations.']
        ],
        'skills' => ['Patient Care', 'Medical Knowledge', 'Empathy', 'Communication', 'Critical Thinking'],
        'icon' => '🏥'
    ],

    'Education' => [
        'code' => 'BEED/BSED',
        'full_name' => 'Bachelor in Elementary/Secondary Education',
        'description' => 'Become a professional teacher equipped with pedagogical skills. Learn curriculum development, classroom management, and educational psychology.',
        'duration' => '4 years',
        'universities' => ['PNU', 'UP Diliman', 'DLSU', 'UST'],
        'careers' => [
            ['title' => 'Elementary Teacher', 'salary' => '₱22,000 - ₱50,000/month', 'demand' => 'High', 'description' => 'Teach children ages 6-12 foundational subjects like reading, math, science, and social studies.'],
            ['title' => 'High School Teacher', 'salary' => '₱25,000 - ₱55,000/month', 'demand' => 'High', 'description' => 'Teach specialized subjects to teenagers, preparing them for college and future careers.'],
            ['title' => 'School Administrator', 'salary' => '₱40,000 - ₱80,000/month', 'demand' => 'Medium', 'description' => 'Manage school operations, coordinate with teachers, and ensure educational standards are met.'],
            ['title' => 'Education Specialist', 'salary' => '₱35,000 - ₱70,000/month', 'demand' => 'Medium', 'description' => 'Develop curriculum, create educational materials, and train teachers on new teaching methods.']
        ],
        'skills' => ['Teaching', 'Communication', 'Patience', 'Creativity', 'Classroom Management'],
        'icon' => '📚'
    ],

    'Communication Arts / Journalism' => [
        'code' => 'ABCOMM',
        'full_name' => 'Bachelor of Arts in Communication / Journalism',
        'description' => 'Master the art of storytelling, media production, and strategic communication. Learn journalism ethics, broadcasting, and public relations.',
        'duration' => '4 years',
        'universities' => ['UP Diliman', 'Ateneo de Manila', 'UST', 'DLSU'],
        'careers' => [
            ['title' => 'Journalist', 'salary' => '₱25,000 - ₱60,000/month', 'demand' => 'Medium', 'description' => 'Research, investigate, and report news stories for newspapers, TV, radio, or online platforms.'],
            ['title' => 'Public Relations Specialist', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'High', 'description' => 'Manage a company\'s public image, handle media relations, and create communication strategies.'],
            ['title' => 'Content Creator', 'salary' => '₱25,000 - ₱100,000/month', 'demand' => 'Very High', 'description' => 'Create engaging content for social media, blogs, videos, and other digital platforms.'],
            ['title' => 'Broadcast Producer', 'salary' => '₱35,000 - ₱90,000/month', 'demand' => 'Medium', 'description' => 'Plan and oversee the production of TV or radio programs, coordinating all aspects of the show.']
        ],
        'skills' => ['Writing', 'Public Speaking', 'Research', 'Media Production', 'Critical Thinking'],
        'icon' => '📰'
    ],

    // Additional courses from dataset
    'Marketing' => [
        'code' => 'BSMKT',
        'full_name' => 'Bachelor of Science in Marketing',
        'description' => 'Learn marketing strategies, consumer behavior, digital marketing, and brand management. Develop skills to drive business growth.',
        'duration' => '4 years',
        'universities' => ['DLSU', 'Ateneo de Manila', 'UP Diliman', 'San Beda'],
        'careers' => [
            ['title' => 'Marketing Manager', 'salary' => '₱40,000 - ₱120,000/month', 'demand' => 'High', 'description' => 'Lead marketing teams, develop strategies, and oversee campaigns to achieve business objectives.'],
            ['title' => 'Digital Marketing Specialist', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'Very High', 'description' => 'Manage online marketing channels including social media, SEO, email marketing, and paid advertising.'],
            ['title' => 'Brand Manager', 'salary' => '₱45,000 - ₱130,000/month', 'demand' => 'High', 'description' => 'Develop and maintain brand identity, ensuring consistent messaging and positioning in the market.'],
            ['title' => 'Market Research Analyst', 'salary' => '₱35,000 - ₱70,000/month', 'demand' => 'Medium', 'description' => 'Gather and analyze data about consumers and competitors to inform marketing strategies.']
        ],
        'skills' => ['Marketing Strategy', 'Analytics', 'Creativity', 'Communication', 'Digital Tools'],
        'icon' => '📈'
    ],

    'Nutrition' => [
        'code' => 'BSND',
        'full_name' => 'Bachelor of Science in Nutrition and Dietetics',
        'description' => 'Study food science, clinical nutrition, and dietary planning. Help individuals and communities achieve optimal health through proper nutrition.',
        'duration' => '4 years',
        'universities' => ['UP Diliman', 'UST', 'CEU', 'Centro Escolar University'],
        'careers' => [
            ['title' => 'Registered Dietitian', 'salary' => '₱25,000 - ₱60,000/month', 'demand' => 'Medium', 'description' => 'Assess nutritional needs and create personalized diet plans for patients in hospitals or clinics.'],
            ['title' => 'Clinical Nutritionist', 'salary' => '₱30,000 - ₱70,000/month', 'demand' => 'Medium', 'description' => 'Work with patients who have medical conditions requiring specialized nutrition therapy.'],
            ['title' => 'Food Service Manager', 'salary' => '₱35,000 - ₱80,000/month', 'demand' => 'Medium', 'description' => 'Oversee food preparation in hospitals, schools, or corporations, ensuring nutritious and safe meals.'],
            ['title' => 'Nutrition Consultant', 'salary' => '₱40,000 - ₱90,000/month', 'demand' => 'Medium', 'description' => 'Provide nutrition advice to individuals, companies, or organizations for wellness programs.']
        ],
        'skills' => ['Nutrition Science', 'Meal Planning', 'Health Education', 'Research', 'Communication'],
        'icon' => '🥗'
    ],

    'Computer Engineering' => [
        'code' => 'BSCpE',
        'full_name' => 'Bachelor of Science in Computer Engineering',
        'description' => 'Combine computer science and electrical engineering. Learn hardware design, embedded systems, and computer architecture.',
        'duration' => '5 years',
        'universities' => ['Mapua University', 'UP Diliman', 'DLSU', 'TIP'],
        'careers' => [
            ['title' => 'Hardware Engineer', 'salary' => '₱40,000 - ₱100,000/month', 'demand' => 'High', 'description' => 'Design and develop computer hardware components like processors, circuit boards, and memory devices.'],
            ['title' => 'Embedded Systems Engineer', 'salary' => '₱45,000 - ₱120,000/month', 'demand' => 'High', 'description' => 'Develop software that runs on embedded devices like cars, appliances, and medical equipment.'],
            ['title' => 'IoT Developer', 'salary' => '₱40,000 - ₱110,000/month', 'demand' => 'Very High', 'description' => 'Build connected devices and systems for the Internet of Things, from smart homes to industrial sensors.'],
            ['title' => 'Systems Architect', 'salary' => '₱60,000 - ₱150,000/month', 'demand' => 'Medium', 'description' => 'Design the overall structure of complex computing systems, ensuring scalability and performance.']
        ],
        'skills' => ['Hardware Design', 'Programming', 'Electronics', 'Problem Solving', 'System Design'],
        'icon' => '🔧'
    ],

    'Architecture' => [
        'code' => 'BSArch',
        'full_name' => 'Bachelor of Science in Architecture',
        'description' => 'Design buildings and structures that are functional, safe, and aesthetically pleasing. Combine art, engineering, and environmental considerations.',
        'duration' => '5 years',
        'universities' => ['UP Diliman', 'UST', 'Mapua University', 'FEU'],
        'careers' => [
            ['title' => 'Licensed Architect', 'salary' => '₱35,000 - ₱100,000/month', 'demand' => 'High', 'description' => 'Design buildings and spaces, create construction plans, and oversee building projects from concept to completion.'],
            ['title' => 'Urban Planner', 'salary' => '₱40,000 - ₱90,000/month', 'demand' => 'Medium', 'description' => 'Plan and design communities, cities, and regions, focusing on land use, transportation, and sustainability.'],
            ['title' => 'Interior Designer', 'salary' => '₱30,000 - ₱80,000/month', 'demand' => 'Medium', 'description' => 'Design interior spaces for homes, offices, and commercial spaces, selecting materials, furniture, and layouts.'],
            ['title' => 'Construction Manager', 'salary' => '₱50,000 - ₱120,000/month', 'demand' => 'High', 'description' => 'Oversee construction projects, manage contractors, ensure safety, and keep projects on schedule and budget.']
        ],
        'skills' => ['Design', 'Technical Drawing', 'Creativity', 'Mathematics', 'Project Management'],
        'icon' => '🏛️'
    ]
]);

/**
 * Get course information by name
 */
function getCourseInfo($courseName) {
    $courses = COURSES;
    return $courses[$courseName] ?? null;
}

/**
 * Get all course names
 */
function getAllCourseNames() {
    return array_keys(COURSES);
}

/**
 * Get interest name by code
 */
function getInterestName($code) {
    $interests = INTERESTS;
    return $interests[$code] ?? 'Unknown';
}

/**
 * Get personality name by code
 */
function getPersonalityName($code) {
    $personalities = PERSONALITIES;
    return $personalities[$code] ?? 'Unknown';
}

