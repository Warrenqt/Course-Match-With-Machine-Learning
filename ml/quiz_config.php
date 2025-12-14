<?php
/**
 * Quiz Configuration
 * 20 General Questions per Course - No prior knowledge needed
 * Questions help students understand what the field is like
 */

// Question Types
define('QUIZ_TYPES', [
    'scenario' => 'What would you do?',
    'preference' => 'Which sounds better?',
    'lifestyle' => 'How do you feel about...',
    'values' => 'What matters to you?'
]);

/**
 * Course name aliases for matching
 */
function getCourseAliases() {
    return [
        'Computer Science' => ['computer science', 'cs', 'compsci', 'bscs', 'computer'],
        'Information Technology' => ['information technology', 'it', 'bsit', 'infotech', 'tech'],
        'Business Administration' => ['business administration', 'business', 'bsba', 'bba', 'management', 'admin'],
        'Nursing' => ['nursing', 'bsn', 'nurse', 'healthcare'],
        'Culinary Arts' => ['culinary arts', 'culinary', 'cooking', 'chef', 'baking', 'hospitality'],
        'Engineering' => ['engineering', 'bsee', 'bsme', 'bsce', 'engineer', 'mechanical', 'electrical', 'civil'],
        'Education' => ['education', 'bsed', 'beed', 'teaching', 'teacher', 'pedagogy'],
        'Accounting' => ['accounting', 'bsa', 'accountancy', 'cpa', 'finance'],
        'Marketing' => ['marketing', 'bsmm', 'advertising', 'sales', 'brand'],
        'Hotel & Restaurant Management' => ['hotel', 'restaurant', 'hrm', 'bshrm', 'tourism', 'hospitality management'],
        'Multimedia Arts / Design' => ['multimedia', 'arts', 'design', 'graphic', 'animation', 'digital arts'],
        'Fine Arts' => ['fine arts', 'bfa', 'painting', 'sculpture', 'visual arts'],
        'Communication Arts / Journalism' => ['communication', 'journalism', 'media', 'broadcasting', 'ab comm', 'journ'],
        'Nutrition' => ['nutrition', 'dietetics', 'bsnd', 'food science', 'diet'],
        'Computer Engineering' => ['computer engineering', 'bscoe', 'coe', 'hardware'],
        'Architecture' => ['architecture', 'bs arch', 'architect', 'bsarch'],
        'Psychology' => ['psychology', 'psych', 'bsa psychology', 'behavioral'],
        'Criminology' => ['criminology', 'bscrim', 'criminal justice', 'law enforcement']
    ];
}

/**
 * Get quiz questions for a specific course
 * Each question has: question, options (A, B, C), correct answer, explanation
 */
function getQuizQuestions($courseName) {
    $allQuestions = getAllCourseQuestions();
    $aliases = getCourseAliases();
    
    // Normalize course name
    $normalizedName = strtolower(trim($courseName));
    
    // First try exact match
    foreach ($allQuestions as $course => $questions) {
        if (strtolower($course) === $normalizedName) {
            return !empty($questions) ? $questions : null;
        }
    }
    
    // Try alias matching
    foreach ($aliases as $course => $aliasArray) {
        foreach ($aliasArray as $alias) {
            if (stripos($normalizedName, $alias) !== false || stripos($alias, $normalizedName) !== false) {
                if (isset($allQuestions[$course]) && !empty($allQuestions[$course])) {
                    return $allQuestions[$course];
                }
            }
        }
    }
    
    // Try partial word match
    foreach ($allQuestions as $course => $questions) {
        if (empty($questions)) continue;
        
        $courseWords = explode(' ', strtolower($course));
        foreach ($courseWords as $word) {
            if (strlen($word) > 3 && stripos($normalizedName, $word) !== false) {
                return $questions;
            }
        }
    }
    
    // Return general questions as fallback
    return $allQuestions['Computer Science'] ?? [];
}

/**
 * Get all course questions
 */
function getAllCourseQuestions() {
    return [
        // ========================================
        // COMPUTER SCIENCE (15 questions, mixed answers)
        // ========================================
        'Computer Science' => [
            [
                'question' => 'You found a bug in an app that crashes when you click a button. How do you feel about spending hours finding the cause?',
                'options' => [
                    'A' => 'That sounds frustrating and boring',
                    'B' => 'I\'d do it if needed, but prefer other tasks',
                    'C' => 'Excited! It\'s like solving a mystery'
                ],
                'correct' => 'C',
                'explanation' => 'Software developers often spend significant time debugging code. Those who enjoy problem-solving find this process rewarding!'
            ],
            [
                'question' => 'How do you feel about sitting in front of a computer for 6-8 hours a day?',
                'options' => [
                    'A' => 'I prefer being active and moving around',
                    'B' => 'That\'s my comfort zone!',
                    'C' => 'I can handle it occasionally'
                ],
                'correct' => 'B',
                'explanation' => 'Most programming jobs involve extended computer work. Screen time is significant in this field.'
            ],
            [
                'question' => 'A new programming language just came out. Your reaction?',
                'options' => [
                    'A' => 'Cool! I want to learn it',
                    'B' => 'Why can\'t we just stick with what works?',
                    'C' => 'I\'ll wait to see if it\'s useful'
                ],
                'correct' => 'A',
                'explanation' => 'Technology evolves rapidly! CS professionals need to continuously learn new tools and languages.'
            ],
            [
                'question' => 'You\'re given a puzzle that seems impossible to solve. What do you do?',
                'options' => [
                    'A' => 'Move on to something else',
                    'B' => 'Try for a while, then ask for help',
                    'C' => 'Keep trying different approaches until I crack it'
                ],
                'correct' => 'C',
                'explanation' => 'Persistence is key in computer science. Complex problems often require multiple attempts!'
            ],
            [
                'question' => 'Math was/is your _____ subject in school.',
                'options' => [
                    'A' => 'Favorite or one of the best',
                    'B' => 'Least favorite, I struggle with it',
                    'C' => 'It\'s okay, I can handle it'
                ],
                'correct' => 'A',
                'explanation' => 'Logical thinking and comfort with numbers helps in algorithms and data science!'
            ],
            [
                'question' => 'When instructions are unclear, you usually:',
                'options' => [
                    'A' => 'Wait for clearer instructions',
                    'B' => 'Figure it out myself by experimenting',
                    'C' => 'Ask questions to clarify'
                ],
                'correct' => 'B',
                'explanation' => 'Self-learning and experimentation are crucial in tech. You often need to figure things out!'
            ],
            [
                'question' => 'If your code works but looks messy, you would:',
                'options' => [
                    'A' => 'If it works, don\'t touch it!',
                    'B' => 'Leave it if there\'s no time',
                    'C' => 'Clean it up even though it works'
                ],
                'correct' => 'C',
                'explanation' => 'Clean code is maintainable code! Good developers write readable, well-organized code.'
            ],
            [
                'question' => 'Someone criticizes your work and suggests improvements. You feel:',
                'options' => [
                    'A' => 'Hurt or annoyed',
                    'B' => 'Grateful - feedback helps me improve',
                    'C' => 'A bit defensive but I\'ll consider it'
                ],
                'correct' => 'B',
                'explanation' => 'Code reviews are standard practice! Being open to feedback is essential for growth.'
            ],
            [
                'question' => 'How interested are you in how apps and websites work "behind the scenes"?',
                'options' => [
                    'A' => 'Very! I always wonder how things work',
                    'B' => 'Not really, I just want them to work',
                    'C' => 'Somewhat curious'
                ],
                'correct' => 'A',
                'explanation' => 'Curiosity about how technology works is a hallmark of successful CS professionals!'
            ],
            [
                'question' => 'A task requires learning something completely new in a week. You think:',
                'options' => [
                    'A' => 'That\'s too much pressure',
                    'B' => 'Challenge accepted!',
                    'C' => 'I\'ll try my best'
                ],
                'correct' => 'B',
                'explanation' => 'The tech industry moves fast. Being comfortable with rapid learning is essential!'
            ],
            [
                'question' => 'Working on a team where everyone codes different parts of a project sounds:',
                'options' => [
                    'A' => 'I prefer working alone',
                    'B' => 'Okay, as long as communication is clear',
                    'C' => 'Great - collaboration makes work better'
                ],
                'correct' => 'C',
                'explanation' => 'Modern software development is highly collaborative. Teams use tools like Git together!'
            ],
            [
                'question' => 'You notice a small inefficiency in a daily process. You:',
                'options' => [
                    'A' => 'Think about how to fix or automate it',
                    'B' => 'Don\'t really notice these things',
                    'C' => 'Note it but move on'
                ],
                'correct' => 'A',
                'explanation' => 'The programmer\'s mindset involves spotting inefficiencies and thinking about automation!'
            ],
            [
                'question' => 'Your friend asks you to build them a simple app. How do you react?',
                'options' => [
                    'A' => 'I\'d rather not deal with that',
                    'B' => 'Excited to create something useful!',
                    'C' => 'Sure, if it\'s not too complicated'
                ],
                'correct' => 'B',
                'explanation' => 'Creating software that helps people is a core motivation for many developers!'
            ],
            [
                'question' => 'How do you feel about working on projects that take months before seeing results?',
                'options' => [
                    'A' => 'Fine - I understand big things take time',
                    'B' => 'I need to see results quickly to stay motivated',
                    'C' => 'It\'s okay but I prefer quick wins'
                ],
                'correct' => 'A',
                'explanation' => 'Software projects can take months or years. Patience is essential for developers!'
            ],
            [
                'question' => 'Learning through reading documentation and tutorials sounds:',
                'options' => [
                    'A' => 'I prefer hands-on or verbal instruction',
                    'B' => 'I can do it when needed',
                    'C' => 'That\'s how I learn best!'
                ],
                'correct' => 'C',
                'explanation' => 'Much of programming knowledge comes from reading docs. Self-directed learning is essential!'
            ]
        ],

        // ========================================
        // BUSINESS ADMINISTRATION (15 questions, mixed answers)
        // ========================================
        'Business Administration' => [
            [
                'question' => 'You\'re put in charge of organizing a group project. How do you feel?',
                'options' => [
                    'A' => 'I\'d rather someone else take charge',
                    'B' => 'Excited! I love coordinating and leading',
                    'C' => 'I can do it if needed'
                ],
                'correct' => 'B',
                'explanation' => 'Business leaders often coordinate teams and projects. Natural leadership tendencies are a great fit!'
            ],
            [
                'question' => 'When making decisions, you prefer to:',
                'options' => [
                    'A' => 'Analyze data and facts first',
                    'B' => 'Ask others what they think',
                    'C' => 'Trust my gut feeling'
                ],
                'correct' => 'A',
                'explanation' => 'Business decisions should be data-driven! Successful managers use metrics and analysis.'
            ],
            [
                'question' => 'Networking events where you meet new professionals sound:',
                'options' => [
                    'A' => 'Uncomfortable and draining',
                    'B' => 'Okay, though sometimes tiring',
                    'C' => 'Like great opportunities to connect'
                ],
                'correct' => 'C',
                'explanation' => 'Networking is crucial in business! Building relationships opens doors to opportunities.'
            ],
            [
                'question' => 'Reading news about the economy, stock market, or business trends:',
                'options' => [
                    'A' => 'Bores me completely',
                    'B' => 'Interests me - I like staying informed',
                    'C' => 'I occasionally check headlines'
                ],
                'correct' => 'B',
                'explanation' => 'Business professionals need to understand economic trends that affect their industry!'
            ],
            [
                'question' => 'Someone disagrees with your idea in a meeting. You:',
                'options' => [
                    'A' => 'Welcome the discussion and defend my points',
                    'B' => 'Prefer to avoid conflict and back down',
                    'C' => 'Feel a bit uncomfortable but engage'
                ],
                'correct' => 'A',
                'explanation' => 'Healthy debate leads to better decisions! Advocating for your ideas professionally is essential.'
            ],
            [
                'question' => 'Managing a budget and tracking expenses sounds:',
                'options' => [
                    'A' => 'Tedious and confusing',
                    'B' => 'Necessary but not exciting',
                    'C' => 'Important and interesting'
                ],
                'correct' => 'C',
                'explanation' => 'Financial literacy is fundamental in business. Managers often oversee budgets!'
            ],
            [
                'question' => 'You have an idea to improve how something works at school/work. You:',
                'options' => [
                    'A' => 'Propose it to whoever can implement it',
                    'B' => 'Keep it to yourself',
                    'C' => 'Mention it casually if asked'
                ],
                'correct' => 'A',
                'explanation' => 'Initiative and proactivity are valued in business. Suggesting improvements shows leadership!'
            ],
            [
                'question' => 'Working on multiple projects simultaneously:',
                'options' => [
                    'A' => 'Stresses me out',
                    'B' => 'Keeps me energized and engaged',
                    'C' => 'Is manageable with good organization'
                ],
                'correct' => 'B',
                'explanation' => 'Business professionals often juggle multiple responsibilities. Multitasking is key!'
            ],
            [
                'question' => 'Making a presentation to convince people of your idea sounds:',
                'options' => [
                    'A' => 'Terrifying',
                    'B' => 'Okay, though I\'d prepare a lot',
                    'C' => 'Exciting - I like persuading others'
                ],
                'correct' => 'C',
                'explanation' => 'Pitching ideas and persuading stakeholders are core business activities!'
            ],
            [
                'question' => 'When a plan isn\'t working, you usually:',
                'options' => [
                    'A' => 'Adapt and try a different approach',
                    'B' => 'Feel stuck and frustrated',
                    'C' => 'Stick with it hoping it improves'
                ],
                'correct' => 'A',
                'explanation' => 'Adaptability is crucial in business! Markets change, and successful leaders pivot when needed.'
            ],
            [
                'question' => 'Being responsible for a team\'s success or failure:',
                'options' => [
                    'A' => 'Is too stressful for me',
                    'B' => 'Is a lot of pressure but manageable',
                    'C' => 'Motivates me to do my best'
                ],
                'correct' => 'C',
                'explanation' => 'Leadership comes with accountability. Great managers embrace this responsibility!'
            ],
            [
                'question' => 'You prefer environments that are:',
                'options' => [
                    'A' => 'Fast-paced with variety',
                    'B' => 'Calm and predictable',
                    'C' => 'Balanced with some routine'
                ],
                'correct' => 'A',
                'explanation' => 'Business environments are often dynamic with new challenges arising frequently!'
            ],
            [
                'question' => 'Negotiating for a better deal or price is something you:',
                'options' => [
                    'A' => 'Avoid if possible',
                    'B' => 'Enjoy and do confidently',
                    'C' => 'Can do when necessary'
                ],
                'correct' => 'B',
                'explanation' => 'Negotiation skills are valuable in business, from salary discussions to vendor contracts!'
            ],
            [
                'question' => 'Taking calculated risks for potential rewards is:',
                'options' => [
                    'A' => 'Too risky for my comfort',
                    'B' => 'Acceptable if well-planned',
                    'C' => 'Exciting and necessary for success'
                ],
                'correct' => 'C',
                'explanation' => 'Business inherently involves risk. Successful managers learn to take smart risks!'
            ],
            [
                'question' => 'If you had your own business, you\'d most enjoy:',
                'options' => [
                    'A' => 'Growing it and making strategic decisions',
                    'B' => 'I\'m not sure I\'d want my own business',
                    'C' => 'The day-to-day operations'
                ],
                'correct' => 'A',
                'explanation' => 'Entrepreneurial thinking and driving growth is at the heart of business!'
            ]
        ],

        // ========================================
        // NURSING (15 questions, mixed answers)
        // ========================================
        'Nursing' => [
            [
                'question' => 'Seeing someone in pain or distress makes you want to:',
                'options' => [
                    'A' => 'Feel uncomfortable and look away',
                    'B' => 'Help them immediately - I can\'t just watch',
                    'C' => 'Help if I know how'
                ],
                'correct' => 'B',
                'explanation' => 'Nurses have a strong desire to help others. This compassionate instinct drives the profession!'
            ],
            [
                'question' => 'Working 12-hour shifts, including nights and weekends:',
                'options' => [
                    'A' => 'Is fine if I\'m helping people',
                    'B' => 'Sounds too difficult for me',
                    'C' => 'Would be challenging but manageable'
                ],
                'correct' => 'A',
                'explanation' => 'Nursing often involves long shifts for 24/7 patient care. Dedication makes it worthwhile!'
            ],
            [
                'question' => 'How do you feel about seeing blood and medical procedures?',
                'options' => [
                    'A' => 'Very squeamish about these things',
                    'B' => 'A little uncomfortable but I can manage',
                    'C' => 'Fine - it doesn\'t bother me'
                ],
                'correct' => 'C',
                'explanation' => 'Nurses regularly encounter blood, wounds, and procedures. Comfort with these is essential!'
            ],
            [
                'question' => 'Staying calm under pressure when things go wrong:',
                'options' => [
                    'A' => 'I tend to panic',
                    'B' => 'I can usually manage to stay focused',
                    'C' => 'It\'s hard but I try'
                ],
                'correct' => 'B',
                'explanation' => 'Medical emergencies require calm, quick thinking. Nurses are often first responders!'
            ],
            [
                'question' => 'Comforting a crying patient or family member would be:',
                'options' => [
                    'A' => 'Very uncomfortable for me',
                    'B' => 'A bit awkward but I\'d try',
                    'C' => 'Something I\'d do naturally'
                ],
                'correct' => 'C',
                'explanation' => 'Emotional support is as important as medical care. Nurses provide comfort during difficult times!'
            ],
            [
                'question' => 'Following strict protocols and rules for patient safety:',
                'options' => [
                    'A' => 'Makes sense - it\'s for everyone\'s protection',
                    'B' => 'I prefer more flexibility',
                    'C' => 'Can feel restrictive but I understand'
                ],
                'correct' => 'A',
                'explanation' => 'Healthcare has strict protocols. Following procedures precisely is critical for safety!'
            ],
            [
                'question' => 'A patient is rude to you. How would you handle it?',
                'options' => [
                    'A' => 'Feel hurt and struggle to continue caring for them',
                    'B' => 'Stay professional - they might be in pain or scared',
                    'C' => 'Try not to take it personally'
                ],
                'correct' => 'B',
                'explanation' => 'Patients may act out due to pain or fear. Professional compassion is key!'
            ],
            [
                'question' => 'Being on your feet most of the day, walking and moving:',
                'options' => [
                    'A' => 'Would exhaust me',
                    'B' => 'Is tiring but acceptable',
                    'C' => 'I prefer active work over sitting'
                ],
                'correct' => 'C',
                'explanation' => 'Nursing is physically demanding. Nurses walk miles during shifts!'
            ],
            [
                'question' => 'Learning about how the human body works:',
                'options' => [
                    'A' => 'Fascinates me',
                    'B' => 'Doesn\'t interest me much',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'A',
                'explanation' => 'Understanding anatomy and physiology is fundamental to nursing education!'
            ],
            [
                'question' => 'Dealing with death and dying patients:',
                'options' => [
                    'A' => 'Scares me too much',
                    'B' => 'Is something I\'d need to learn to handle',
                    'C' => 'Would be hard but I want to provide comfort'
                ],
                'correct' => 'C',
                'explanation' => 'End-of-life care is part of nursing. Providing dignity and comfort is meaningful work.'
            ],
            [
                'question' => 'Washing, bathing, or helping someone with personal hygiene:',
                'options' => [
                    'A' => 'Is part of caring for someone - I can do it',
                    'B' => 'Is something I really wouldn\'t want to do',
                    'C' => 'Would be awkward but manageable'
                ],
                'correct' => 'A',
                'explanation' => 'Personal care assistance is significant in nursing, especially with elderly patients!'
            ],
            [
                'question' => 'Making quick decisions that affect someone\'s health:',
                'options' => [
                    'A' => 'Is too much responsibility for me',
                    'B' => 'I can think quickly under pressure',
                    'C' => 'Makes me nervous but I\'d try'
                ],
                'correct' => 'B',
                'explanation' => 'Nurses often need to make rapid assessments. Critical thinking saves lives!'
            ],
            [
                'question' => 'The opportunity to work abroad or travel for nursing:',
                'options' => [
                    'A' => 'Excites me - nursing is in demand worldwide',
                    'B' => 'Doesn\'t appeal to me',
                    'C' => 'Is interesting to consider'
                ],
                'correct' => 'A',
                'explanation' => 'Filipino nurses are highly valued globally! Many work abroad in the US, UK, and Middle East.'
            ],
            [
                'question' => 'Making a difference in someone\'s life, even in small ways:',
                'options' => [
                    'A' => 'I don\'t think about this much',
                    'B' => 'Would be nice',
                    'C' => 'Is deeply meaningful to me'
                ],
                'correct' => 'C',
                'explanation' => 'Nursing provides daily opportunities to positively impact lives!'
            ],
            [
                'question' => 'Memorizing detailed medical information and procedures:',
                'options' => [
                    'A' => 'I struggle with detailed memorization',
                    'B' => 'I\'m good at remembering important details',
                    'C' => 'Takes effort but I can do it'
                ],
                'correct' => 'B',
                'explanation' => 'Nursing requires learning extensive medical knowledge - medications, procedures, and protocols!'
            ]
        ],

        // ========================================
        // CULINARY ARTS (15 questions, mixed answers)
        // ========================================
        'Culinary Arts' => [
            [
                'question' => 'When you cook something, you:',
                'options' => [
                    'A' => 'Cook only when necessary',
                    'B' => 'Follow recipes closely',
                    'C' => 'Love experimenting and trying new flavors'
                ],
                'correct' => 'C',
                'explanation' => 'Great chefs are creative! Experimenting with flavors is how signature dishes are born.'
            ],
            [
                'question' => 'Working in a hot, fast-paced kitchen environment:',
                'options' => [
                    'A' => 'Sounds energizing and exciting',
                    'B' => 'Sounds stressful and uncomfortable',
                    'C' => 'Could be challenging but manageable'
                ],
                'correct' => 'A',
                'explanation' => 'Professional kitchens are intense! The heat and pressure are part of culinary life.'
            ],
            [
                'question' => 'Standing and working for 10+ hours with few breaks:',
                'options' => [
                    'A' => 'Is too physically demanding',
                    'B' => 'Is fine if I\'m doing what I love',
                    'C' => 'Would be tough but doable'
                ],
                'correct' => 'B',
                'explanation' => 'Culinary work is physically demanding. Long hours on your feet are standard!'
            ],
            [
                'question' => 'Receiving criticism about a dish you made:',
                'options' => [
                    'A' => 'Would really hurt my feelings',
                    'B' => 'Stings a bit but I\'ll learn from it',
                    'C' => 'Helps me improve - I want honest feedback'
                ],
                'correct' => 'C',
                'explanation' => 'Chefs must accept feedback gracefully. Every criticism is a chance to improve!'
            ],
            [
                'question' => 'Learning about different cuisines and food cultures:',
                'options' => [
                    'A' => 'Fascinates me - I want to try everything',
                    'B' => 'I prefer sticking to familiar foods',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'A',
                'explanation' => 'Culinary education exposes you to global cuisines. The best chefs draw from many cultures!'
            ],
            [
                'question' => 'Creating beautiful food presentations:',
                'options' => [
                    'A' => 'Isn\'t important to me',
                    'B' => 'Excites me - food should look amazing',
                    'C' => 'Is nice but taste matters more'
                ],
                'correct' => 'B',
                'explanation' => 'We eat with our eyes first! Plating and presentation are art forms in fine dining.'
            ],
            [
                'question' => 'Working during holidays, weekends, and evenings:',
                'options' => [
                    'A' => 'Is expected - that\'s when people dine out',
                    'B' => 'Would be a deal-breaker',
                    'C' => 'Is acceptable occasionally'
                ],
                'correct' => 'A',
                'explanation' => 'Restaurants are busiest when others are off! Holidays are often the biggest workdays.'
            ],
            [
                'question' => 'Handling raw meat, fish, and various ingredients:',
                'options' => [
                    'A' => 'Grosses me out',
                    'B' => 'Takes some getting used to',
                    'C' => 'Doesn\'t bother me at all'
                ],
                'correct' => 'C',
                'explanation' => 'Chefs work with all types of ingredients daily. Comfort with raw proteins is necessary!'
            ],
            [
                'question' => 'Following a head chef\'s orders exactly, even if you disagree:',
                'options' => [
                    'A' => 'Goes against my nature',
                    'B' => 'Is part of learning - I\'ll earn my voice later',
                    'C' => 'Would be frustrating but I\'d comply'
                ],
                'correct' => 'B',
                'explanation' => 'Kitchen hierarchy is strict! Starting cooks follow orders and learn before they lead.'
            ],
            [
                'question' => 'The idea of starting your own restaurant someday:',
                'options' => [
                    'A' => 'Sounds like too much work',
                    'B' => 'Would be interesting',
                    'C' => 'Is my dream!'
                ],
                'correct' => 'C',
                'explanation' => 'Many culinary students dream of owning restaurants. The entrepreneurial spirit is common!'
            ],
            [
                'question' => 'Working in a team where timing and coordination are critical:',
                'options' => [
                    'A' => 'I prefer working alone',
                    'B' => 'I thrive in team environments',
                    'C' => 'I can work with others'
                ],
                'correct' => 'B',
                'explanation' => 'Kitchen teams must synchronize perfectly. Every dish must come out at the right time!'
            ],
            [
                'question' => 'Tasting and adjusting flavors until a dish is perfect:',
                'options' => [
                    'A' => 'Good enough is good enough',
                    'B' => 'Is satisfying - the details matter',
                    'C' => 'Is important but time-consuming'
                ],
                'correct' => 'B',
                'explanation' => 'Great chefs are perfectionists about flavor! Constant tasting and adjustment is key.'
            ],
            [
                'question' => 'Seeing someone enjoy a meal you prepared:',
                'options' => [
                    'A' => 'Is the best feeling!',
                    'B' => 'Doesn\'t affect me much',
                    'C' => 'Is nice'
                ],
                'correct' => 'A',
                'explanation' => 'The joy of feeding people drives many chefs. Creating happiness through food is rewarding!'
            ],
            [
                'question' => 'Repetitively chopping, prepping, and cleaning:',
                'options' => [
                    'A' => 'Sounds boring and tedious',
                    'B' => 'Is meditative - I find it satisfying',
                    'C' => 'Is part of the job, I\'ll do it'
                ],
                'correct' => 'B',
                'explanation' => 'Kitchen prep takes hours daily! Finding satisfaction in these tasks is essential for chefs.'
            ],
            [
                'question' => 'Learning the science behind cooking (chemistry, temperatures):',
                'options' => [
                    'A' => 'Interests me - I want to understand why things work',
                    'B' => 'I\'d rather just cook by feel',
                    'C' => 'Is useful information'
                ],
                'correct' => 'A',
                'explanation' => 'Understanding food science helps chefs troubleshoot and innovate!'
            ]
        ],

        // ========================================
        // INFORMATION TECHNOLOGY (15 questions, mixed answers)
        // ========================================
        'Information Technology' => [
            [
                'question' => 'When a friend\'s computer has a problem, you usually:',
                'options' => [
                    'A' => 'Suggest they take it to a technician',
                    'B' => 'Help if it\'s a simple issue',
                    'C' => 'Enjoy troubleshooting and fixing it'
                ],
                'correct' => 'C',
                'explanation' => 'IT professionals love solving tech problems! Being the go-to tech person is a sign you might enjoy this field.'
            ],
            [
                'question' => 'Setting up networks, servers, and systems sounds:',
                'options' => [
                    'A' => 'Interesting and challenging',
                    'B' => 'Too complex for me',
                    'C' => 'Technical but learnable'
                ],
                'correct' => 'A',
                'explanation' => 'IT professionals manage the infrastructure that keeps organizations running!'
            ],
            [
                'question' => 'Being on-call to fix urgent tech problems outside office hours:',
                'options' => [
                    'A' => 'Is not something I want',
                    'B' => 'Is part of keeping systems running smoothly',
                    'C' => 'Would be okay occasionally'
                ],
                'correct' => 'B',
                'explanation' => 'IT often involves on-call duties because technology problems can happen anytime!'
            ],
            [
                'question' => 'Learning about cybersecurity and protecting systems from hackers:',
                'options' => [
                    'A' => 'Doesn\'t excite me',
                    'B' => 'Fascinates me - it\'s like digital defense',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'B',
                'explanation' => 'Cybersecurity is a growing field within IT. Protecting data and systems is critical!'
            ],
            [
                'question' => 'Explaining technical concepts to non-technical people:',
                'options' => [
                    'A' => 'It\'s frustrating when people don\'t understand',
                    'B' => 'I can do it if needed',
                    'C' => 'I enjoy making complex things simple'
                ],
                'correct' => 'C',
                'explanation' => 'IT professionals constantly communicate with users who need tech support!'
            ],
            [
                'question' => 'The idea of managing a company\'s entire technology infrastructure:',
                'options' => [
                    'A' => 'Sounds like important, impactful work',
                    'B' => 'Is too much pressure',
                    'C' => 'Is a lot of responsibility'
                ],
                'correct' => 'A',
                'explanation' => 'IT managers oversee all technology systems. It\'s a high-impact role!'
            ],
            [
                'question' => 'Working with databases and managing data:',
                'options' => [
                    'A' => 'Sounds boring',
                    'B' => 'Interests me - data is powerful',
                    'C' => 'Seems useful to learn'
                ],
                'correct' => 'B',
                'explanation' => 'Database management is a core IT skill. Data drives modern business decisions!'
            ],
            [
                'question' => 'Keeping up with new technology trends and tools:',
                'options' => [
                    'A' => 'Excites me - I love learning new tech',
                    'B' => 'Is overwhelming',
                    'C' => 'Is necessary for the job'
                ],
                'correct' => 'A',
                'explanation' => 'Technology evolves rapidly. IT professionals need continuous learning!'
            ],
            [
                'question' => 'Being the person everyone calls when tech isn\'t working:',
                'options' => [
                    'A' => 'Would get annoying',
                    'B' => 'Makes me feel helpful and valued',
                    'C' => 'Is okay most of the time'
                ],
                'correct' => 'B',
                'explanation' => 'IT support roles involve helping people solve tech problems daily!'
            ],
            [
                'question' => 'Creating documentation and user guides for systems:',
                'options' => [
                    'A' => 'Helps everyone understand and use systems properly',
                    'B' => 'Is tedious paperwork',
                    'C' => 'Is necessary but not exciting'
                ],
                'correct' => 'A',
                'explanation' => 'Good documentation is crucial in IT for training and troubleshooting!'
            ],
            [
                'question' => 'Troubleshooting a problem you\'ve never seen before:',
                'options' => [
                    'A' => 'Is stressful and frustrating',
                    'B' => 'Is a fun challenge to solve',
                    'C' => 'I\'d rather pass it to someone else'
                ],
                'correct' => 'B',
                'explanation' => 'IT professionals often face new problems. Problem-solving skills are essential!'
            ],
            [
                'question' => 'Installing and configuring software for users:',
                'options' => [
                    'A' => 'Is tedious work',
                    'B' => 'Is part of keeping things running',
                    'C' => 'Is satisfying when everything works'
                ],
                'correct' => 'C',
                'explanation' => 'Software deployment is a core IT task. Seeing systems work smoothly is rewarding!'
            ],
            [
                'question' => 'Working in a server room with lots of equipment:',
                'options' => [
                    'A' => 'Sounds interesting - I like hardware',
                    'B' => 'Sounds noisy and cold',
                    'C' => 'I prefer just working with software'
                ],
                'correct' => 'A',
                'explanation' => 'IT involves physical infrastructure too. Server rooms house critical equipment!'
            ],
            [
                'question' => 'Helping users who are frustrated with technology:',
                'options' => [
                    'A' => 'Tests my patience too much',
                    'B' => 'I stay calm and help them',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'IT support requires patience. Users rely on you when technology fails them!'
            ],
            [
                'question' => 'Learning how to automate repetitive IT tasks:',
                'options' => [
                    'A' => 'Excites me - work smarter not harder',
                    'B' => 'Seems too complicated',
                    'C' => 'I prefer doing things manually'
                ],
                'correct' => 'A',
                'explanation' => 'Automation is a key skill in modern IT. Scripts save hours of repetitive work!'
            ]
        ],

        // ========================================
        // ENGINEERING (15 questions, mixed answers)
        // ========================================
        'Engineering' => [
            [
                'question' => 'When something breaks, you usually:',
                'options' => [
                    'A' => 'Call someone to repair it',
                    'B' => 'Try to figure out how to fix it yourself',
                    'C' => 'Look up how to fix it'
                ],
                'correct' => 'B',
                'explanation' => 'Engineers love understanding how things work and solving mechanical problems!'
            ],
            [
                'question' => 'How do you feel about physics and advanced math?',
                'options' => [
                    'A' => 'Challenging but interesting',
                    'B' => 'I struggle with these subjects',
                    'C' => 'I can handle them'
                ],
                'correct' => 'A',
                'explanation' => 'Engineering is built on physics and mathematics. Strong foundations are essential!'
            ],
            [
                'question' => 'Building or constructing things with your hands:',
                'options' => [
                    'A' => 'Isn\'t really my thing',
                    'B' => 'Is okay sometimes',
                    'C' => 'Is satisfying and enjoyable'
                ],
                'correct' => 'C',
                'explanation' => 'Many engineers enjoy hands-on work, from prototypes to construction!'
            ],
            [
                'question' => 'Solving complex problems that have multiple possible solutions:',
                'options' => [
                    'A' => 'Is confusing and stressful',
                    'B' => 'Excites me - I like finding the best approach',
                    'C' => 'Is challenging but doable'
                ],
                'correct' => 'B',
                'explanation' => 'Engineering is about finding optimal solutions to complex challenges!'
            ],
            [
                'question' => 'Learning about how buildings, bridges, or machines are designed:',
                'options' => [
                    'A' => 'Fascinates me',
                    'B' => 'Doesn\'t interest me',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'A',
                'explanation' => 'Civil and mechanical engineering focus on designing structures and machines!'
            ],
            [
                'question' => 'Following strict safety codes and standards:',
                'options' => [
                    'A' => 'Feels restrictive',
                    'B' => 'Makes sense for safety',
                    'C' => 'Is essential - lives depend on it'
                ],
                'correct' => 'C',
                'explanation' => 'Engineering has strict safety standards. A bridge or building must be safe!'
            ],
            [
                'question' => 'Using computer software to design and simulate projects:',
                'options' => [
                    'A' => 'Sounds like a powerful tool',
                    'B' => 'Seems complicated',
                    'C' => 'Is something I\'d learn'
                ],
                'correct' => 'A',
                'explanation' => 'Modern engineering uses CAD software, simulations, and modeling extensively!'
            ],
            [
                'question' => 'Working in construction sites or industrial environments:',
                'options' => [
                    'A' => 'Sounds uncomfortable',
                    'B' => 'Would be exciting to see projects come to life',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'Many engineers work on-site to oversee construction and manufacturing!'
            ],
            [
                'question' => 'Creating something that improves people\'s lives:',
                'options' => [
                    'A' => 'Is deeply motivating to me',
                    'B' => 'I don\'t think about this',
                    'C' => 'Would be nice'
                ],
                'correct' => 'A',
                'explanation' => 'Engineers create solutions that improve society - roads, clean water, power!'
            ],
            [
                'question' => 'Working on projects that take months or years to complete:',
                'options' => [
                    'A' => 'I prefer quick results',
                    'B' => 'Is fine - big projects need time',
                    'C' => 'Requires patience but I can do it'
                ],
                'correct' => 'B',
                'explanation' => 'Engineering projects often take years from design to completion!'
            ],
            [
                'question' => 'Collaborating with other engineers and specialists:',
                'options' => [
                    'A' => 'I prefer working alone',
                    'B' => 'Is how great projects get built',
                    'C' => 'Is okay when needed'
                ],
                'correct' => 'B',
                'explanation' => 'Engineering projects require teamwork across multiple disciplines!'
            ],
            [
                'question' => 'Calculating loads, stresses, and material requirements:',
                'options' => [
                    'A' => 'Sounds like important precision work',
                    'B' => 'Is too detailed for me',
                    'C' => 'Is part of engineering'
                ],
                'correct' => 'A',
                'explanation' => 'Engineers must calculate precisely to ensure structures and machines are safe!'
            ],
            [
                'question' => 'Understanding how electricity, mechanics, or structures work:',
                'options' => [
                    'A' => 'Doesn\'t interest me',
                    'B' => 'Fascinates me - I want to know the details',
                    'C' => 'Is useful knowledge'
                ],
                'correct' => 'B',
                'explanation' => 'Different engineering branches focus on electrical, mechanical, or structural systems!'
            ],
            [
                'question' => 'Taking responsibility for the safety of your designs:',
                'options' => [
                    'A' => 'Is what being an engineer is about',
                    'B' => 'Is too much pressure',
                    'C' => 'Is a serious responsibility'
                ],
                'correct' => 'A',
                'explanation' => 'Engineers sign off on designs. Professional responsibility is core to the profession!'
            ],
            [
                'question' => 'Studying for professional licensure exams:',
                'options' => [
                    'A' => 'Is too stressful',
                    'B' => 'Is worth it for career advancement',
                    'C' => 'Is challenging but achievable'
                ],
                'correct' => 'B',
                'explanation' => 'Professional Engineer (PE) licensure requires passing rigorous exams!'
            ]
        ],

        // ========================================
        // EDUCATION (15 questions, mixed answers)
        // ========================================
        'Education' => [
            [
                'question' => 'Explaining a difficult concept until someone understands:',
                'options' => [
                    'A' => 'Is frustrating if it takes too long',
                    'B' => 'Is rewarding - I love that "aha" moment',
                    'C' => 'Is fine if they\'re willing to learn'
                ],
                'correct' => 'B',
                'explanation' => 'Teachers find joy in helping students understand. That breakthrough moment is priceless!'
            ],
            [
                'question' => 'Being patient with people who learn slowly:',
                'options' => [
                    'A' => 'Tests my patience',
                    'B' => 'Requires effort but I can do it',
                    'C' => 'Comes naturally - everyone learns differently'
                ],
                'correct' => 'C',
                'explanation' => 'Patience is perhaps the most important trait for educators!'
            ],
            [
                'question' => 'Standing in front of a class and speaking:',
                'options' => [
                    'A' => 'Feels natural or exciting to me',
                    'B' => 'Is something I\'d rather avoid',
                    'C' => 'Makes me nervous but I can manage'
                ],
                'correct' => 'A',
                'explanation' => 'Teachers spend most of their day communicating and presenting to students!'
            ],
            [
                'question' => 'Creating engaging activities and lessons:',
                'options' => [
                    'A' => 'Sounds time-consuming',
                    'B' => 'Sounds creative and fun',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'Great teachers design lessons that engage and inspire students!'
            ],
            [
                'question' => 'Working with children or teenagers daily:',
                'options' => [
                    'A' => 'Would be exhausting',
                    'B' => 'Would be okay',
                    'C' => 'Sounds enjoyable and energizing'
                ],
                'correct' => 'C',
                'explanation' => 'Teachers work with young people all day. Enjoying their energy is essential!'
            ],
            [
                'question' => 'Handling discipline issues and conflicts in class:',
                'options' => [
                    'A' => 'Is part of guiding young people',
                    'B' => 'Is something I\'d dread',
                    'C' => 'Would be challenging'
                ],
                'correct' => 'A',
                'explanation' => 'Classroom management is a key teaching skill. Maintaining order helps learning!'
            ],
            [
                'question' => 'Grading papers and providing feedback:',
                'options' => [
                    'A' => 'Sounds tedious',
                    'B' => 'Is time-consuming but necessary',
                    'C' => 'Helps students improve - it\'s valuable work'
                ],
                'correct' => 'C',
                'explanation' => 'Teachers spend significant time assessing work and giving feedback!'
            ],
            [
                'question' => 'Making a difference in someone\'s future through education:',
                'options' => [
                    'A' => 'Is deeply meaningful to me',
                    'B' => 'I don\'t think about this much',
                    'C' => 'Would be nice'
                ],
                'correct' => 'A',
                'explanation' => 'Many teachers are motivated by the lasting impact they have on students\' lives!'
            ],
            [
                'question' => 'Adapting your approach for different learning styles:',
                'options' => [
                    'A' => 'Seems complicated',
                    'B' => 'Makes sense - not everyone learns the same way',
                    'C' => 'Is challenging but important'
                ],
                'correct' => 'B',
                'explanation' => 'Effective teachers adapt their methods to reach all students!'
            ],
            [
                'question' => 'Continuing to learn and improve your teaching methods:',
                'options' => [
                    'A' => 'Is essential for being a good teacher',
                    'B' => 'I\'d prefer to stick with what works',
                    'C' => 'Is something I\'d do'
                ],
                'correct' => 'A',
                'explanation' => 'Great teachers are lifelong learners who constantly improve their craft!'
            ],
            [
                'question' => 'Being a role model for young people:',
                'options' => [
                    'A' => 'Is too much pressure',
                    'B' => 'Is a responsibility I\'d take seriously',
                    'C' => 'Comes with the territory'
                ],
                'correct' => 'B',
                'explanation' => 'Teachers influence students beyond academics. Character and behavior matter!'
            ],
            [
                'question' => 'Communicating with parents about their children:',
                'options' => [
                    'A' => 'Is important for student success',
                    'B' => 'Can be awkward',
                    'C' => 'I\'d rather just focus on teaching'
                ],
                'correct' => 'A',
                'explanation' => 'Parent-teacher communication is essential for supporting students!'
            ],
            [
                'question' => 'Earning a modest but stable salary:',
                'options' => [
                    'A' => 'Is not enough motivation',
                    'B' => 'Is fine if the work is meaningful',
                    'C' => 'Is acceptable'
                ],
                'correct' => 'B',
                'explanation' => 'Teaching salaries are modest, but many find purpose outweighs pay!'
            ],
            [
                'question' => 'Preparing students for exams and assessments:',
                'options' => [
                    'A' => 'Is stressful',
                    'B' => 'Is part of helping them succeed',
                    'C' => 'Is necessary'
                ],
                'correct' => 'B',
                'explanation' => 'Teachers help students prepare for assessments that affect their futures!'
            ],
            [
                'question' => 'Seeing former students succeed in life:',
                'options' => [
                    'A' => 'Is the greatest reward of teaching',
                    'B' => 'Would be nice to hear about',
                    'C' => 'Wouldn\'t affect me much'
                ],
                'correct' => 'A',
                'explanation' => 'Many teachers find joy in knowing they contributed to their students\' success!'
            ]
        ],

        // ========================================
        // ACCOUNTING (15 questions, mixed answers)
        // ========================================
        'Accounting' => [
            [
                'question' => 'Working with numbers and spreadsheets for hours:',
                'options' => [
                    'A' => 'Sounds tedious',
                    'B' => 'Is comfortable and engaging for me',
                    'C' => 'Is manageable'
                ],
                'correct' => 'B',
                'explanation' => 'Accountants work extensively with numbers, spreadsheets, and financial data!'
            ],
            [
                'question' => 'Finding errors in financial records or calculations:',
                'options' => [
                    'A' => 'Sounds frustrating',
                    'B' => 'Is part of ensuring accuracy',
                    'C' => 'Is satisfying - I have an eye for detail'
                ],
                'correct' => 'C',
                'explanation' => 'Accountants must catch errors. Attention to detail is crucial!'
            ],
            [
                'question' => 'Following strict rules and regulations:',
                'options' => [
                    'A' => 'Makes sense - it ensures fairness and accuracy',
                    'B' => 'Feels restrictive',
                    'C' => 'Is necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Accounting follows strict standards. Compliance is non-negotiable!'
            ],
            [
                'question' => 'Helping businesses understand their financial health:',
                'options' => [
                    'A' => 'Doesn\'t excite me',
                    'B' => 'Sounds like valuable, important work',
                    'C' => 'Is interesting'
                ],
                'correct' => 'B',
                'explanation' => 'Accountants help businesses make informed financial decisions!'
            ],
            [
                'question' => 'Working independently with minimal supervision:',
                'options' => [
                    'A' => 'Suits me well',
                    'B' => 'I prefer more guidance',
                    'C' => 'Is okay'
                ],
                'correct' => 'A',
                'explanation' => 'Many accountants work independently on financial analysis and reports!'
            ],
            [
                'question' => 'Meeting strict deadlines for financial reports and taxes:',
                'options' => [
                    'A' => 'Is too much pressure',
                    'B' => 'Motivates me to stay organized',
                    'C' => 'Is stressful but manageable'
                ],
                'correct' => 'B',
                'explanation' => 'Tax season and quarterly reports have firm deadlines. Time management is key!'
            ],
            [
                'question' => 'Studying for and passing professional certification exams (like CPA):',
                'options' => [
                    'A' => 'Sounds too difficult',
                    'B' => 'Is worth the effort for career advancement',
                    'C' => 'Is challenging but necessary'
                ],
                'correct' => 'B',
                'explanation' => 'CPA certification opens doors but requires passing rigorous exams!'
            ],
            [
                'question' => 'Being trusted with confidential financial information:',
                'options' => [
                    'A' => 'Is a responsibility I take seriously',
                    'B' => 'Makes me uncomfortable',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'A',
                'explanation' => 'Accountants handle sensitive financial data and must maintain confidentiality!'
            ],
            [
                'question' => 'Making sure every number is accurate and accounted for:',
                'options' => [
                    'A' => 'Good enough is good enough',
                    'B' => 'Is important',
                    'C' => 'Is essential - small errors can have big consequences'
                ],
                'correct' => 'C',
                'explanation' => 'In accounting, precision matters. A small error can have major implications!'
            ],
            [
                'question' => 'Working long hours during tax season or audits:',
                'options' => [
                    'A' => 'Is expected during busy periods',
                    'B' => 'Is not something I want',
                    'C' => 'Would be tough but manageable'
                ],
                'correct' => 'A',
                'explanation' => 'Tax season means long hours for accountants. It\'s intense but temporary!'
            ],
            [
                'question' => 'Analyzing financial statements to spot trends:',
                'options' => [
                    'A' => 'Is like detective work with numbers',
                    'B' => 'Seems overwhelming',
                    'C' => 'Is useful for businesses'
                ],
                'correct' => 'A',
                'explanation' => 'Accountants analyze financials to help businesses understand their performance!'
            ],
            [
                'question' => 'Preparing tax returns for individuals or companies:',
                'options' => [
                    'A' => 'Sounds boring',
                    'B' => 'Is satisfying when it\'s done right',
                    'C' => 'Is necessary work'
                ],
                'correct' => 'B',
                'explanation' => 'Tax preparation is a core accounting skill. Getting it right saves clients money!'
            ],
            [
                'question' => 'Working in a corporate office environment:',
                'options' => [
                    'A' => 'Suits my preference',
                    'B' => 'Is too confining',
                    'C' => 'Is acceptable'
                ],
                'correct' => 'A',
                'explanation' => 'Most accountants work in office settings, either in firms or corporate departments!'
            ],
            [
                'question' => 'Explaining financial concepts to non-financial people:',
                'options' => [
                    'A' => 'Is frustrating',
                    'B' => 'Is part of being a good accountant',
                    'C' => 'Takes patience'
                ],
                'correct' => 'B',
                'explanation' => 'Accountants often explain financial matters to clients and managers!'
            ],
            [
                'question' => 'Staying updated on changing tax laws and accounting standards:',
                'options' => [
                    'A' => 'Is necessary for the profession',
                    'B' => 'Sounds overwhelming',
                    'C' => 'Keeps the work interesting'
                ],
                'correct' => 'C',
                'explanation' => 'Tax laws and accounting standards change. Continuous learning is essential!'
            ]
        ],

        // ========================================
        // MARKETING (15 questions, mixed answers)
        // ========================================
        'Marketing' => [
            [
                'question' => 'Coming up with creative ideas for advertising:',
                'options' => [
                    'A' => 'Isn\'t really my strength',
                    'B' => 'Is interesting sometimes',
                    'C' => 'Excites me - I love brainstorming concepts'
                ],
                'correct' => 'C',
                'explanation' => 'Marketing thrives on creativity! Developing compelling campaigns is core to the job.'
            ],
            [
                'question' => 'Understanding why people buy certain products:',
                'options' => [
                    'A' => 'Fascinates me - consumer psychology is interesting',
                    'B' => 'Doesn\'t interest me much',
                    'C' => 'Makes sense to learn'
                ],
                'correct' => 'A',
                'explanation' => 'Marketing is about understanding consumer behavior and motivations!'
            ],
            [
                'question' => 'Using social media for business purposes:',
                'options' => [
                    'A' => 'Feels inauthentic',
                    'B' => 'Is exciting - I love digital platforms',
                    'C' => 'Is useful to learn'
                ],
                'correct' => 'B',
                'explanation' => 'Digital and social media marketing are huge parts of modern marketing!'
            ],
            [
                'question' => 'Analyzing data to see if campaigns are working:',
                'options' => [
                    'A' => 'Is important for making smart decisions',
                    'B' => 'Sounds boring',
                    'C' => 'Is necessary but not my favorite part'
                ],
                'correct' => 'A',
                'explanation' => 'Modern marketing is data-driven. Measuring results is essential!'
            ],
            [
                'question' => 'Presenting ideas and pitching to clients:',
                'options' => [
                    'A' => 'Is something I\'d avoid',
                    'B' => 'Sounds exciting - I like persuading people',
                    'C' => 'Would make me nervous but I\'d try'
                ],
                'correct' => 'B',
                'explanation' => 'Marketers often pitch campaigns to clients and stakeholders!'
            ],
            [
                'question' => 'Working in a fast-paced environment with tight deadlines:',
                'options' => [
                    'A' => 'Keeps me energized',
                    'B' => 'Is too much pressure',
                    'C' => 'Is stressful but manageable'
                ],
                'correct' => 'A',
                'explanation' => 'Marketing campaigns often have tight timelines and require quick turnarounds!'
            ],
            [
                'question' => 'Staying current with trends and pop culture:',
                'options' => [
                    'A' => 'Doesn\'t interest me',
                    'B' => 'Is fun - I\'m always aware of what\'s trending',
                    'C' => 'Is useful for the job'
                ],
                'correct' => 'B',
                'explanation' => 'Marketers need to stay on top of trends to create relevant campaigns!'
            ],
            [
                'question' => 'Writing catchy copy and compelling messages:',
                'options' => [
                    'A' => 'Isn\'t my strength',
                    'B' => 'Is a skill I\'d enjoy developing',
                    'C' => 'Is challenging but learnable'
                ],
                'correct' => 'B',
                'explanation' => 'Copywriting is essential in marketing - words sell products!'
            ],
            [
                'question' => 'Building a brand and its image:',
                'options' => [
                    'A' => 'Sounds like creative, strategic work',
                    'B' => 'Seems abstract',
                    'C' => 'Is interesting'
                ],
                'correct' => 'A',
                'explanation' => 'Brand building is a key marketing function - creating identity and perception!'
            ],
            [
                'question' => 'Working with different teams (design, sales, product):',
                'options' => [
                    'A' => 'I prefer working alone',
                    'B' => 'Is exciting - collaboration brings better results',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'Marketing works closely with other departments to align messaging!'
            ],
            [
                'question' => 'Studying what competitors are doing:',
                'options' => [
                    'A' => 'Is fascinating - I like competitive analysis',
                    'B' => 'Seems like copying',
                    'C' => 'Is useful for strategy'
                ],
                'correct' => 'A',
                'explanation' => 'Competitive analysis helps marketers find opportunities and differentiate!'
            ],
            [
                'question' => 'Creating content that goes viral:',
                'options' => [
                    'A' => 'Is unpredictable and frustrating',
                    'B' => 'Is an exciting challenge',
                    'C' => 'Depends on luck'
                ],
                'correct' => 'B',
                'explanation' => 'Viral content is a marketer\'s dream. It takes creativity and understanding audiences!'
            ],
            [
                'question' => 'Measuring success with metrics like clicks, conversions, and engagement:',
                'options' => [
                    'A' => 'Sounds too technical',
                    'B' => 'Is how you prove value',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'Marketing success is measured by data. Metrics prove campaign effectiveness!'
            ],
            [
                'question' => 'Influencing people\'s purchasing decisions:',
                'options' => [
                    'A' => 'Is exciting - it\'s powerful work',
                    'B' => 'Feels manipulative',
                    'C' => 'Is what marketing does'
                ],
                'correct' => 'A',
                'explanation' => 'Marketing influences decisions. Ethical marketers help people find products they need!'
            ],
            [
                'question' => 'Launching new products or campaigns:',
                'options' => [
                    'A' => 'Is stressful',
                    'B' => 'Is thrilling - I love launch days',
                    'C' => 'Is part of the cycle'
                ],
                'correct' => 'B',
                'explanation' => 'Product launches are exciting! Seeing campaigns go live is rewarding for marketers.'
            ]
        ],

        // ========================================
        // HOTEL & RESTAURANT MANAGEMENT (15 questions, mixed answers)
        // ========================================
        'Hotel & Restaurant Management' => [
            [
                'question' => 'Making guests feel welcome and comfortable:',
                'options' => [
                    'A' => 'Isn\'t really my style',
                    'B' => 'Comes naturally - I love hosting',
                    'C' => 'Is something I can do'
                ],
                'correct' => 'B',
                'explanation' => 'Hospitality is about making people feel welcomed and cared for!'
            ],
            [
                'question' => 'Working during holidays, weekends, and evenings:',
                'options' => [
                    'A' => 'Is a deal-breaker',
                    'B' => 'Is expected - that\'s when people travel and dine out',
                    'C' => 'Is manageable occasionally'
                ],
                'correct' => 'B',
                'explanation' => 'Hotels and restaurants are busiest when others are off!'
            ],
            [
                'question' => 'Handling customer complaints calmly:',
                'options' => [
                    'A' => 'I can stay calm and find solutions',
                    'B' => 'Would frustrate me',
                    'C' => 'Is challenging but I\'d try'
                ],
                'correct' => 'A',
                'explanation' => 'Guest satisfaction is priority #1. Handling complaints well is crucial!'
            ],
            [
                'question' => 'Managing multiple tasks simultaneously in a busy environment:',
                'options' => [
                    'A' => 'Is overwhelming',
                    'B' => 'Keeps me engaged and energized',
                    'C' => 'Is stressful but manageable'
                ],
                'correct' => 'B',
                'explanation' => 'Hotels and restaurants require constant multitasking!'
            ],
            [
                'question' => 'Paying attention to small details (cleanliness, presentation):',
                'options' => [
                    'A' => 'Matters a lot to me - details create experiences',
                    'B' => 'I focus on bigger things',
                    'C' => 'Is important'
                ],
                'correct' => 'A',
                'explanation' => 'In hospitality, small details make the difference between good and exceptional!'
            ],
            [
                'question' => 'Learning about food, wine, and beverage service:',
                'options' => [
                    'A' => 'Doesn\'t interest me',
                    'B' => 'Interests me somewhat',
                    'C' => 'Fascinates me'
                ],
                'correct' => 'C',
                'explanation' => 'F&B knowledge is essential in hotel and restaurant management!'
            ],
            [
                'question' => 'Supervising and motivating staff:',
                'options' => [
                    'A' => 'Sounds stressful',
                    'B' => 'Is something I\'d enjoy',
                    'C' => 'Is part of management'
                ],
                'correct' => 'B',
                'explanation' => 'Managers lead teams. Motivating staff improves service quality!'
            ],
            [
                'question' => 'Working in a people-facing role all day:',
                'options' => [
                    'A' => 'Energizes me - I love interacting with people',
                    'B' => 'Would drain me',
                    'C' => 'Is okay'
                ],
                'correct' => 'A',
                'explanation' => 'Hospitality is a people business. Enjoying interaction is essential!'
            ],
            [
                'question' => 'Starting in entry-level positions before management:',
                'options' => [
                    'A' => 'Is frustrating',
                    'B' => 'Makes sense - you need to understand all roles',
                    'C' => 'Is acceptable'
                ],
                'correct' => 'B',
                'explanation' => 'Most hotel managers worked their way up, understanding every department!'
            ],
            [
                'question' => 'The idea of working in different countries or travel destinations:',
                'options' => [
                    'A' => 'Excites me - hospitality opens global doors',
                    'B' => 'Doesn\'t appeal to me',
                    'C' => 'Sounds interesting'
                ],
                'correct' => 'A',
                'explanation' => 'Hospitality skills are globally transferable. Many work internationally!'
            ],
            [
                'question' => 'Ensuring every guest has an exceptional experience:',
                'options' => [
                    'A' => 'Is what I\'d strive for every day',
                    'B' => 'Is impossible to achieve',
                    'C' => 'Is a good goal'
                ],
                'correct' => 'A',
                'explanation' => 'Exceptional guest experiences drive repeat business and word-of-mouth!'
            ],
            [
                'question' => 'Being on your feet and active throughout your shift:',
                'options' => [
                    'A' => 'Is tiring',
                    'B' => 'Is better than sitting at a desk all day',
                    'C' => 'Is part of the industry'
                ],
                'correct' => 'B',
                'explanation' => 'Hospitality is an active industry. You\'re moving and engaged constantly!'
            ],
            [
                'question' => 'Managing room bookings, events, and reservations:',
                'options' => [
                    'A' => 'Sounds organizational and satisfying',
                    'B' => 'Sounds complicated',
                    'C' => 'Is part of operations'
                ],
                'correct' => 'A',
                'explanation' => 'Reservation and event management are core hospitality functions!'
            ],
            [
                'question' => 'Creating memorable experiences for special occasions:',
                'options' => [
                    'A' => 'Is extra work',
                    'B' => 'Is what makes hospitality special',
                    'C' => 'Is nice to do'
                ],
                'correct' => 'B',
                'explanation' => 'Hotels and restaurants host celebrations. Making them memorable is rewarding!'
            ],
            [
                'question' => 'Learning about different cultures and their customs:',
                'options' => [
                    'A' => 'Is essential for serving diverse guests',
                    'B' => 'Is interesting',
                    'C' => 'Isn\'t necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Hospitality serves guests from around the world. Cultural sensitivity matters!'
            ]
        ],

        // ========================================
        // MULTIMEDIA ARTS / DESIGN (15 questions, mixed answers)
        // ========================================
        'Multimedia Arts / Design' => [
            [
                'question' => 'Expressing ideas visually through art or design:',
                'options' => [
                    'A' => 'Isn\'t my strength',
                    'B' => 'Is something I love and do naturally',
                    'C' => 'Is interesting to try'
                ],
                'correct' => 'B',
                'explanation' => 'Designers communicate ideas visually. Creative expression is at the core!'
            ],
            [
                'question' => 'Learning to use design software (Photoshop, Illustrator, etc.):',
                'options' => [
                    'A' => 'Seems complicated',
                    'B' => 'Is necessary for the job',
                    'C' => 'Excites me - I want to master these tools'
                ],
                'correct' => 'C',
                'explanation' => 'Proficiency in design software is essential for multimedia careers!'
            ],
            [
                'question' => 'Receiving criticism about your creative work:',
                'options' => [
                    'A' => 'Is too personal and hurtful',
                    'B' => 'Helps me improve - feedback is valuable',
                    'C' => 'Is difficult but I understand its value'
                ],
                'correct' => 'B',
                'explanation' => 'Designers regularly receive feedback. Using it to improve is key!'
            ],
            [
                'question' => 'Revising work multiple times until it\'s perfect:',
                'options' => [
                    'A' => 'Is part of the creative process',
                    'B' => 'Is frustrating',
                    'C' => 'Is tedious but necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Design involves many iterations. Refinement leads to great work!'
            ],
            [
                'question' => 'Working on video, animation, or motion graphics:',
                'options' => [
                    'A' => 'Fascinates me - I love moving visuals',
                    'B' => 'Doesn\'t interest me',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'A',
                'explanation' => 'Multimedia arts include video, animation, and motion design!'
            ],
            [
                'question' => 'Balancing artistic vision with client requirements:',
                'options' => [
                    'A' => 'Would be frustrating',
                    'B' => 'Is a creative challenge I\'d enjoy',
                    'C' => 'Is part of professional work'
                ],
                'correct' => 'B',
                'explanation' => 'Commercial design requires balancing creativity with client needs!'
            ],
            [
                'question' => 'Staying current with design trends and visual styles:',
                'options' => [
                    'A' => 'Is exciting - I\'m always looking at design',
                    'B' => 'Doesn\'t interest me',
                    'C' => 'Is useful for the job'
                ],
                'correct' => 'A',
                'explanation' => 'Design trends evolve. Staying current keeps your work relevant!'
            ],
            [
                'question' => 'Meeting tight deadlines for creative projects:',
                'options' => [
                    'A' => 'Limits my creativity',
                    'B' => 'Pushes me to be creative under pressure',
                    'C' => 'Is stressful but manageable'
                ],
                'correct' => 'B',
                'explanation' => 'Creative industries have tight deadlines. Performing under pressure is essential!'
            ],
            [
                'question' => 'Building a portfolio to showcase your work:',
                'options' => [
                    'A' => 'Feels like showing off',
                    'B' => 'Is necessary for getting hired',
                    'C' => 'Is exciting - I\'m proud of my creative work'
                ],
                'correct' => 'C',
                'explanation' => 'Your portfolio is your calling card. Designers are hired based on their work!'
            ],
            [
                'question' => 'Working on different types of projects (branding, web, video):',
                'options' => [
                    'A' => 'Keeps things interesting - I like variety',
                    'B' => 'I prefer specializing in one area',
                    'C' => 'Is okay'
                ],
                'correct' => 'A',
                'explanation' => 'Multimedia artists often work across various media and project types!'
            ],
            [
                'question' => 'Creating designs that look good on screens and print:',
                'options' => [
                    'A' => 'Requires understanding different mediums',
                    'B' => 'Is confusing',
                    'C' => 'Is an interesting technical challenge'
                ],
                'correct' => 'C',
                'explanation' => 'Designers create for multiple formats. Understanding each medium is important!'
            ],
            [
                'question' => 'Collaborating with photographers, writers, and developers:',
                'options' => [
                    'A' => 'Creates better final products',
                    'B' => 'Is part of the job',
                    'C' => 'I prefer working alone'
                ],
                'correct' => 'A',
                'explanation' => 'Multimedia projects involve many specialists working together!'
            ],
            [
                'question' => 'Learning new design tools and techniques:',
                'options' => [
                    'A' => 'Is overwhelming',
                    'B' => 'Is exciting - the field keeps evolving',
                    'C' => 'Is necessary'
                ],
                'correct' => 'B',
                'explanation' => 'Design technology changes rapidly. Embracing new tools keeps you competitive!'
            ],
            [
                'question' => 'Spending hours perfecting small visual details:',
                'options' => [
                    'A' => 'Is satisfying - details matter',
                    'B' => 'Is tedious',
                    'C' => 'Good enough is good enough'
                ],
                'correct' => 'A',
                'explanation' => 'Great design is in the details. Attention to small elements elevates work!'
            ],
            [
                'question' => 'Working freelance or in an agency:',
                'options' => [
                    'A' => 'Both sound like good options',
                    'B' => 'Prefer stable employment',
                    'C' => 'I\'m not sure'
                ],
                'correct' => 'A',
                'explanation' => 'Designers can work in agencies, in-house, or freelance. Flexibility is a perk!'
            ]
        ],

        // ========================================
        // FINE ARTS (15 questions, mixed answers)
        // ========================================
        'Fine Arts' => [
            [
                'question' => 'Creating art as a form of personal expression:',
                'options' => [
                    'A' => 'Isn\'t really me',
                    'B' => 'Is something I enjoy',
                    'C' => 'Is essential to who I am'
                ],
                'correct' => 'C',
                'explanation' => 'Fine artists create from personal vision and expression!'
            ],
            [
                'question' => 'Spending hours perfecting a single piece:',
                'options' => [
                    'A' => 'Tests my patience',
                    'B' => 'Is meditative and satisfying',
                    'C' => 'Is acceptable for important work'
                ],
                'correct' => 'B',
                'explanation' => 'Fine art often requires intense focus and time on each piece!'
            ],
            [
                'question' => 'Studying art history and the works of master artists:',
                'options' => [
                    'A' => 'Inspires and educates me',
                    'B' => 'Bores me',
                    'C' => 'Is interesting'
                ],
                'correct' => 'A',
                'explanation' => 'Understanding art history provides context and inspiration!'
            ],
            [
                'question' => 'Pursuing art even if financial success isn\'t guaranteed:',
                'options' => [
                    'A' => 'Is too unstable for me',
                    'B' => 'Is worth it if I\'m doing what I love',
                    'C' => 'Is risky but I\'d try'
                ],
                'correct' => 'B',
                'explanation' => 'Art careers can be financially challenging. Passion often drives artists!'
            ],
            [
                'question' => 'Working with different mediums (paint, sculpture, mixed media):',
                'options' => [
                    'A' => 'Excites me - I love experimenting',
                    'B' => 'I prefer sticking to one medium',
                    'C' => 'Is interesting to try'
                ],
                'correct' => 'A',
                'explanation' => 'Fine arts education exposes you to various mediums and techniques!'
            ],
            [
                'question' => 'Having your art critiqued by professors and peers:',
                'options' => [
                    'A' => 'Is too personal',
                    'B' => 'Is valuable for growth as an artist',
                    'C' => 'Is difficult but necessary'
                ],
                'correct' => 'B',
                'explanation' => 'Art critiques help artists develop their skills and vision!'
            ],
            [
                'question' => 'Creating art that may be controversial or challenging:',
                'options' => [
                    'A' => 'Is part of artistic expression',
                    'B' => 'Makes me uncomfortable',
                    'C' => 'Is something I\'d consider carefully'
                ],
                'correct' => 'A',
                'explanation' => 'Art often challenges perspectives. Bold expression is part of the practice!'
            ],
            [
                'question' => 'Working independently on your own projects:',
                'options' => [
                    'A' => 'Suits my creative process',
                    'B' => 'I prefer working with others',
                    'C' => 'Is okay but I like collaboration too'
                ],
                'correct' => 'A',
                'explanation' => 'Fine artists often work independently on personal projects!'
            ],
            [
                'question' => 'Exhibiting your work for public viewing:',
                'options' => [
                    'A' => 'Makes me feel vulnerable',
                    'B' => 'Is necessary for recognition',
                    'C' => 'Is exciting - I want to share my vision'
                ],
                'correct' => 'C',
                'explanation' => 'Exhibitions are how artists share their work with the world!'
            ],
            [
                'question' => 'Seeing art as a lifelong pursuit rather than just a career:',
                'options' => [
                    'A' => 'Resonates with me - art is my calling',
                    'B' => 'I see it more as a job',
                    'C' => 'Is one way to look at it'
                ],
                'correct' => 'A',
                'explanation' => 'Many fine artists see their work as a lifelong calling and identity!'
            ],
            [
                'question' => 'Drawing inspiration from your emotions and experiences:',
                'options' => [
                    'A' => 'Is how I naturally create',
                    'B' => 'Is too personal',
                    'C' => 'Is one approach'
                ],
                'correct' => 'A',
                'explanation' => 'Fine art often comes from personal emotions and experiences!'
            ],
            [
                'question' => 'Developing your own unique artistic style:',
                'options' => [
                    'A' => 'Is important for standing out',
                    'B' => 'Is my goal',
                    'C' => 'Takes too long'
                ],
                'correct' => 'B',
                'explanation' => 'Finding your artistic voice is a key goal for fine artists!'
            ],
            [
                'question' => 'Selling your artwork or working on commissions:',
                'options' => [
                    'A' => 'Feels like selling out',
                    'B' => 'Is necessary to make a living',
                    'C' => 'Is rewarding when others value my work'
                ],
                'correct' => 'C',
                'explanation' => 'Artists can make a living through sales and commissions!'
            ],
            [
                'question' => 'Taking creative risks that might not work out:',
                'options' => [
                    'A' => 'Is part of growth as an artist',
                    'B' => 'Is too risky',
                    'C' => 'Is sometimes necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Artistic growth requires experimentation and risk-taking!'
            ],
            [
                'question' => 'Visiting galleries, museums, and art shows:',
                'options' => [
                    'A' => 'Bores me',
                    'B' => 'Is inspiring and educational',
                    'C' => 'Is okay sometimes'
                ],
                'correct' => 'B',
                'explanation' => 'Engaging with art in person inspires and educates artists!'
            ]
        ],

        // ========================================
        // COMMUNICATION ARTS / JOURNALISM (15 questions, mixed answers)
        // ========================================
        'Communication Arts / Journalism' => [
            [
                'question' => 'Writing stories or articles:',
                'options' => [
                    'A' => 'Isn\'t my strength',
                    'B' => 'Is something I love and do well',
                    'C' => 'Is something I can do'
                ],
                'correct' => 'B',
                'explanation' => 'Writing is fundamental to journalism and communication!'
            ],
            [
                'question' => 'Interviewing people and asking probing questions:',
                'options' => [
                    'A' => 'Makes me uncomfortable',
                    'B' => 'Would be interesting',
                    'C' => 'Sounds exciting - I love hearing stories'
                ],
                'correct' => 'C',
                'explanation' => 'Journalists interview sources to get the story. It requires curiosity!'
            ],
            [
                'question' => 'Working under tight deadlines for breaking news:',
                'options' => [
                    'A' => 'Is too much pressure',
                    'B' => 'Is thrilling - I work well under pressure',
                    'C' => 'Is stressful but manageable'
                ],
                'correct' => 'B',
                'explanation' => 'News waits for no one! Journalists must deliver quickly and accurately.'
            ],
            [
                'question' => 'Staying informed about current events and news:',
                'options' => [
                    'A' => 'I\'m always reading news and staying updated',
                    'B' => 'I don\'t follow news much',
                    'C' => 'I keep up occasionally'
                ],
                'correct' => 'A',
                'explanation' => 'Journalists need to be well-informed about what\'s happening in the world!'
            ],
            [
                'question' => 'Speaking on camera or on radio:',
                'options' => [
                    'A' => 'Sounds exciting - I\'d love to try',
                    'B' => 'Is something I\'d avoid',
                    'C' => 'Would make me nervous but I\'d try'
                ],
                'correct' => 'A',
                'explanation' => 'Broadcast journalism involves speaking on camera or radio!'
            ],
            [
                'question' => 'Researching and verifying facts thoroughly:',
                'options' => [
                    'A' => 'Is tedious',
                    'B' => 'Is essential - accuracy matters',
                    'C' => 'Is necessary'
                ],
                'correct' => 'B',
                'explanation' => 'Journalists must verify information. Credibility depends on accuracy!'
            ],
            [
                'question' => 'Covering stories that may be controversial or sensitive:',
                'options' => [
                    'A' => 'Is important - the public needs to know',
                    'B' => 'Is something I\'d rather avoid',
                    'C' => 'Is challenging but part of the job'
                ],
                'correct' => 'A',
                'explanation' => 'Journalists cover difficult topics. Public interest journalism serves society!'
            ],
            [
                'question' => 'Building relationships with sources and contacts:',
                'options' => [
                    'A' => 'Feels too transactional',
                    'B' => 'Is interesting - networking opens doors',
                    'C' => 'Is necessary for the job'
                ],
                'correct' => 'B',
                'explanation' => 'Good journalism relies on trusted sources and relationships!'
            ],
            [
                'question' => 'Adapting writing style for different platforms:',
                'options' => [
                    'A' => 'Is a useful skill to develop',
                    'B' => 'Seems inconsistent',
                    'C' => 'Is part of modern journalism'
                ],
                'correct' => 'A',
                'explanation' => 'Modern journalists write for multiple platforms with different styles!'
            ],
            [
                'question' => 'Using your work to inform or influence public opinion:',
                'options' => [
                    'A' => 'Seems manipulative',
                    'B' => 'Is a meaningful responsibility',
                    'C' => 'Is a lot of power'
                ],
                'correct' => 'B',
                'explanation' => 'Journalists shape public discourse. It\'s a responsibility taken seriously!'
            ],
            [
                'question' => 'Telling stories that give voice to the voiceless:',
                'options' => [
                    'A' => 'Is what draws me to journalism',
                    'B' => 'Is one type of journalism',
                    'C' => 'Isn\'t my focus'
                ],
                'correct' => 'A',
                'explanation' => 'Journalism can amplify marginalized voices and create social change!'
            ],
            [
                'question' => 'Dealing with criticism or backlash from your reporting:',
                'options' => [
                    'A' => 'Comes with the territory',
                    'B' => 'Would be hard to handle',
                    'C' => 'Is part of standing by your work'
                ],
                'correct' => 'C',
                'explanation' => 'Journalists sometimes face criticism. Standing by accurate reporting is key!'
            ],
            [
                'question' => 'Working irregular hours to cover events:',
                'options' => [
                    'A' => 'Is part of chasing stories',
                    'B' => 'Is too unpredictable',
                    'C' => 'Is acceptable sometimes'
                ],
                'correct' => 'A',
                'explanation' => 'News happens anytime. Journalists often work irregular hours!'
            ],
            [
                'question' => 'Creating content for social media and digital platforms:',
                'options' => [
                    'A' => 'Is where journalism is heading',
                    'B' => 'Is less legitimate than traditional media',
                    'C' => 'Is useful to learn'
                ],
                'correct' => 'A',
                'explanation' => 'Digital journalism is the future. Social platforms are key distribution channels!'
            ],
            [
                'question' => 'Being objective and presenting multiple perspectives:',
                'options' => [
                    'A' => 'Is challenging but important',
                    'B' => 'Is impossible - everyone has bias',
                    'C' => 'Is what good journalism requires'
                ],
                'correct' => 'C',
                'explanation' => 'Journalistic objectivity and fairness build credibility and trust!'
            ]
        ],

        // ========================================
        // ARCHITECTURE (15 questions, mixed answers)
        // ========================================
        'Architecture' => [
            [
                'question' => 'Looking at buildings and analyzing their design:',
                'options' => [
                    'A' => 'Is not something I think about',
                    'B' => 'Is something I do naturally',
                    'C' => 'Is interesting sometimes'
                ],
                'correct' => 'B',
                'explanation' => 'Architects see buildings differently - analyzing form, function, and aesthetics!'
            ],
            [
                'question' => 'Combining artistic creativity with technical precision:',
                'options' => [
                    'A' => 'Is too demanding',
                    'B' => 'Is an exciting challenge',
                    'C' => 'Is manageable'
                ],
                'correct' => 'B',
                'explanation' => 'Architecture balances art and engineering. Both skills are essential!'
            ],
            [
                'question' => 'Studying for 5+ years including internships:',
                'options' => [
                    'A' => 'Is worth it for the career',
                    'B' => 'Is too long for me',
                    'C' => 'Is a long commitment'
                ],
                'correct' => 'A',
                'explanation' => 'Architecture requires extensive education and licensure!'
            ],
            [
                'question' => 'Creating detailed technical drawings and plans:',
                'options' => [
                    'A' => 'Sounds tedious',
                    'B' => 'Sounds precise and satisfying',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'Architectural drawings must be precise for construction!'
            ],
            [
                'question' => 'Designing spaces that improve people\'s lives:',
                'options' => [
                    'A' => 'Is deeply meaningful to me',
                    'B' => 'I don\'t think about this',
                    'C' => 'Would be nice'
                ],
                'correct' => 'A',
                'explanation' => 'Architecture shapes how people live, work, and interact with space!'
            ],
            [
                'question' => 'Working on projects that take years to complete:',
                'options' => [
                    'A' => 'Would test my patience too much',
                    'B' => 'Requires patience',
                    'C' => 'Is rewarding to see the final building'
                ],
                'correct' => 'C',
                'explanation' => 'Major architectural projects can take many years from design to completion!'
            ],
            [
                'question' => 'Learning building codes, regulations, and safety standards:',
                'options' => [
                    'A' => 'Is essential - buildings must be safe',
                    'B' => 'Sounds boring',
                    'C' => 'Is necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Architects must ensure buildings comply with codes and are safe!'
            ],
            [
                'question' => 'Using 3D modeling software and design technology:',
                'options' => [
                    'A' => 'Seems complicated',
                    'B' => 'Is exciting - I love seeing designs come to life',
                    'C' => 'Is a tool I\'d learn'
                ],
                'correct' => 'B',
                'explanation' => 'Modern architects use advanced software for design and visualization!'
            ],
            [
                'question' => 'Collaborating with engineers, contractors, and clients:',
                'options' => [
                    'A' => 'Is part of bringing designs to reality',
                    'B' => 'I prefer working alone',
                    'C' => 'Is necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Architecture is highly collaborative. Teamwork brings buildings to life!'
            ],
            [
                'question' => 'Considering environmental sustainability in design:',
                'options' => [
                    'A' => 'Isn\'t my priority',
                    'B' => 'Is increasingly important and interesting',
                    'C' => 'Is a good consideration'
                ],
                'correct' => 'B',
                'explanation' => 'Sustainable design is a growing priority in architecture!'
            ],
            [
                'question' => 'Visiting construction sites to oversee projects:',
                'options' => [
                    'A' => 'Is part of seeing designs become reality',
                    'B' => 'Sounds uncomfortable',
                    'C' => 'Is necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Architects often visit sites to ensure construction matches their designs!'
            ],
            [
                'question' => 'Creating models and renderings of your designs:',
                'options' => [
                    'A' => 'Is satisfying - I love seeing ideas take form',
                    'B' => 'Takes too long',
                    'C' => 'Is part of the process'
                ],
                'correct' => 'A',
                'explanation' => 'Models and renderings help clients visualize architectural designs!'
            ],
            [
                'question' => 'Balancing client wishes with practical constraints:',
                'options' => [
                    'A' => 'Is frustrating',
                    'B' => 'Is a creative challenge',
                    'C' => 'Is part of the job'
                ],
                'correct' => 'B',
                'explanation' => 'Architects must balance creativity with budget and structural realities!'
            ],
            [
                'question' => 'Understanding how people interact with spaces:',
                'options' => [
                    'A' => 'Fascinates me',
                    'B' => 'Is interesting',
                    'C' => 'Isn\'t something I think about'
                ],
                'correct' => 'A',
                'explanation' => 'Human-centered design considers how people use and experience spaces!'
            ],
            [
                'question' => 'Leaving a lasting physical legacy through your buildings:',
                'options' => [
                    'A' => 'Is motivating - buildings outlast us',
                    'B' => 'Is a lot of responsibility',
                    'C' => 'I don\'t think about legacy'
                ],
                'correct' => 'A',
                'explanation' => 'Architecture creates lasting structures that serve communities for generations!'
            ]
        ],

        // ========================================
        // NUTRITION (15 questions, mixed answers)
        // ========================================
        'Nutrition' => [
            [
                'question' => 'Learning about how food affects the body:',
                'options' => [
                    'A' => 'Doesn\'t interest me much',
                    'B' => 'Fascinates me - I love understanding nutrition',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'B',
                'explanation' => 'Nutritionists study how food impacts health, energy, and wellbeing!'
            ],
            [
                'question' => 'Helping people make healthier food choices:',
                'options' => [
                    'A' => 'People should figure it out themselves',
                    'B' => 'Sounds like good work',
                    'C' => 'Is meaningful work I\'d enjoy'
                ],
                'correct' => 'C',
                'explanation' => 'Nutritionists counsel clients on improving their diets and health!'
            ],
            [
                'question' => 'Studying biology, chemistry, and health sciences:',
                'options' => [
                    'A' => 'Interests me - I want to understand the science',
                    'B' => 'Sounds too difficult',
                    'C' => 'Is necessary for the degree'
                ],
                'correct' => 'A',
                'explanation' => 'Nutrition science requires understanding biochemistry and physiology!'
            ],
            [
                'question' => 'Creating meal plans for different health conditions:',
                'options' => [
                    'A' => 'Seems complicated',
                    'B' => 'Sounds like a valuable skill',
                    'C' => 'Is interesting'
                ],
                'correct' => 'B',
                'explanation' => 'Dietitians create specialized meal plans for various health needs!'
            ],
            [
                'question' => 'Working with patients who may resist changing their habits:',
                'options' => [
                    'A' => 'Would be frustrating',
                    'B' => 'I\'d be patient and encouraging',
                    'C' => 'Would be challenging'
                ],
                'correct' => 'B',
                'explanation' => 'Changing eating habits is hard. Nutritionists must be patient counselors!'
            ],
            [
                'question' => 'Staying updated on nutrition research and guidelines:',
                'options' => [
                    'A' => 'Is important - nutrition science evolves',
                    'B' => 'Seems like too much reading',
                    'C' => 'Is necessary'
                ],
                'correct' => 'A',
                'explanation' => 'Nutrition recommendations change as research progresses!'
            ],
            [
                'question' => 'Working in hospitals, clinics, or wellness centers:',
                'options' => [
                    'A' => 'Doesn\'t appeal to me',
                    'B' => 'Sounds like meaningful healthcare work',
                    'C' => 'Would be okay'
                ],
                'correct' => 'B',
                'explanation' => 'Nutritionists work in various healthcare and wellness settings!'
            ],
            [
                'question' => 'Calculating nutritional values and dietary requirements:',
                'options' => [
                    'A' => 'Sounds tedious',
                    'B' => 'Is part of the job',
                    'C' => 'Is precise work I\'d find satisfying'
                ],
                'correct' => 'C',
                'explanation' => 'Nutritionists calculate specific nutrient needs for clients!'
            ],
            [
                'question' => 'Promoting overall health and disease prevention:',
                'options' => [
                    'A' => 'Is meaningful - prevention is better than cure',
                    'B' => 'I prefer treating existing problems',
                    'C' => 'Is important work'
                ],
                'correct' => 'A',
                'explanation' => 'Nutrition plays a key role in preventing chronic diseases!'
            ],
            [
                'question' => 'Passing a licensure exam to practice as a registered dietitian:',
                'options' => [
                    'A' => 'Sounds stressful',
                    'B' => 'Is worth the effort for professional credibility',
                    'C' => 'Is necessary for the career'
                ],
                'correct' => 'B',
                'explanation' => 'Registered Dietitians must pass licensure exams to practice!'
            ],
            [
                'question' => 'Debunking nutrition myths and misinformation:',
                'options' => [
                    'A' => 'Is frustrating',
                    'B' => 'Is important for public health',
                    'C' => 'Is part of education'
                ],
                'correct' => 'B',
                'explanation' => 'Nutritionists help people distinguish facts from fads!'
            ],
            [
                'question' => 'Working with athletes to optimize their performance:',
                'options' => [
                    'A' => 'Sounds like exciting, specialized work',
                    'B' => 'Is too specialized',
                    'C' => 'Is one career option'
                ],
                'correct' => 'A',
                'explanation' => 'Sports nutrition is a growing specialty within the field!'
            ],
            [
                'question' => 'Educating communities about healthy eating:',
                'options' => [
                    'A' => 'Is impactful public health work',
                    'B' => 'Is okay',
                    'C' => 'Isn\'t my interest'
                ],
                'correct' => 'A',
                'explanation' => 'Community nutrition education can improve population health!'
            ],
            [
                'question' => 'Reading food labels and understanding ingredients:',
                'options' => [
                    'A' => 'Is boring',
                    'B' => 'Is something I already do',
                    'C' => 'Is useful to know'
                ],
                'correct' => 'B',
                'explanation' => 'Nutritionists are experts at interpreting food labels and ingredients!'
            ],
            [
                'question' => 'Understanding the emotional relationship people have with food:',
                'options' => [
                    'A' => 'Is key to helping people change',
                    'B' => 'Is complicated',
                    'C' => 'Isn\'t really nutrition'
                ],
                'correct' => 'A',
                'explanation' => 'Nutrition counseling often addresses emotional and psychological factors!'
            ]
        ],

        // ========================================
        // COMPUTER ENGINEERING (15 questions, mixed answers)
        // ========================================
        'Computer Engineering' => [
            [
                'question' => 'Understanding how computer hardware works:',
                'options' => [
                    'A' => 'Doesn\'t interest me',
                    'B' => 'Fascinates me - I want to know what\'s inside',
                    'C' => 'Interests me somewhat'
                ],
                'correct' => 'B',
                'explanation' => 'Computer engineers work with both hardware and software!'
            ],
            [
                'question' => 'Combining electrical engineering with computer science:',
                'options' => [
                    'A' => 'I prefer one or the other',
                    'B' => 'Is a lot to learn',
                    'C' => 'Sounds like the best of both worlds'
                ],
                'correct' => 'C',
                'explanation' => 'Computer engineering bridges hardware and software disciplines!'
            ],
            [
                'question' => 'Building circuits and working with electronic components:',
                'options' => [
                    'A' => 'Is hands-on work I\'d enjoy',
                    'B' => 'Doesn\'t appeal to me',
                    'C' => 'Is interesting to try'
                ],
                'correct' => 'A',
                'explanation' => 'Computer engineers design and build electronic systems!'
            ],
            [
                'question' => 'Writing code that interfaces with hardware:',
                'options' => [
                    'A' => 'I prefer pure software',
                    'B' => 'Sounds like interesting, practical programming',
                    'C' => 'Is part of the field'
                ],
                'correct' => 'B',
                'explanation' => 'Embedded systems programming connects software to hardware!'
            ],
            [
                'question' => 'Studying advanced math and physics:',
                'options' => [
                    'A' => 'Is challenging but interesting',
                    'B' => 'Is too difficult for me',
                    'C' => 'Is necessary for the degree'
                ],
                'correct' => 'A',
                'explanation' => 'Computer engineering requires strong math and physics foundations!'
            ],
            [
                'question' => 'Working on projects like robotics or IoT devices:',
                'options' => [
                    'A' => 'Seems too complex',
                    'B' => 'Excites me - I love building smart devices',
                    'C' => 'Sounds interesting'
                ],
                'correct' => 'B',
                'explanation' => 'Computer engineers work on robotics, IoT, and embedded systems!'
            ],
            [
                'question' => 'Troubleshooting both hardware and software issues:',
                'options' => [
                    'A' => 'Sounds complicated',
                    'B' => 'Is part of the job',
                    'C' => 'Is a skill I\'d value having'
                ],
                'correct' => 'C',
                'explanation' => 'Computer engineers debug at both hardware and software levels!'
            ],
            [
                'question' => 'Designing systems that need to be highly reliable:',
                'options' => [
                    'A' => 'Is critical - lives may depend on it',
                    'B' => 'Is too much responsibility',
                    'C' => 'Is important'
                ],
                'correct' => 'A',
                'explanation' => 'Many projects (medical devices, vehicles) require high reliability!'
            ],
            [
                'question' => 'Working in industries like aerospace, automotive, or medical devices:',
                'options' => [
                    'A' => 'Doesn\'t particularly interest me',
                    'B' => 'Sounds exciting and impactful',
                    'C' => 'Would be interesting'
                ],
                'correct' => 'B',
                'explanation' => 'Computer engineers work in many high-tech industries!'
            ],
            [
                'question' => 'Optimizing systems for performance and efficiency:',
                'options' => [
                    'A' => 'Is satisfying - making things work better',
                    'B' => 'Is too detail-oriented',
                    'C' => 'Is part of engineering'
                ],
                'correct' => 'A',
                'explanation' => 'Computer engineers optimize hardware and software performance!'
            ],
            [
                'question' => 'Understanding low-level programming (assembly, drivers):',
                'options' => [
                    'A' => 'Sounds too complicated',
                    'B' => 'Is fascinating - it\'s close to the metal',
                    'C' => 'Is useful to know'
                ],
                'correct' => 'B',
                'explanation' => 'Low-level programming gives direct control over hardware!'
            ],
            [
                'question' => 'Designing chips and processors:',
                'options' => [
                    'A' => 'Is too abstract',
                    'B' => 'Sounds like cutting-edge work',
                    'C' => 'Is interesting'
                ],
                'correct' => 'B',
                'explanation' => 'Chip design is a specialized field within computer engineering!'
            ],
            [
                'question' => 'Testing and validating hardware systems:',
                'options' => [
                    'A' => 'Is tedious',
                    'B' => 'Is crucial for quality',
                    'C' => 'Is necessary'
                ],
                'correct' => 'B',
                'explanation' => 'Testing ensures hardware works correctly before production!'
            ],
            [
                'question' => 'Working in a lab with oscilloscopes and testing equipment:',
                'options' => [
                    'A' => 'Sounds hands-on and interesting',
                    'B' => 'Sounds intimidating',
                    'C' => 'Is part of the field'
                ],
                'correct' => 'A',
                'explanation' => 'Computer engineers use specialized lab equipment for testing!'
            ],
            [
                'question' => 'Creating products that people use daily (phones, laptops, cars):',
                'options' => [
                    'A' => 'Is motivating - I\'d love to contribute',
                    'B' => 'Is one career path',
                    'C' => 'I prefer behind-the-scenes work'
                ],
                'correct' => 'A',
                'explanation' => 'Computer engineers design the technology we use every day!'
            ]
        ]
    ];
}

/**
 * Calculate quiz score
 */
function calculateQuizScore($answers, $questions) {
    $correct = 0;
    $total = count($questions);
    
    foreach ($answers as $index => $answer) {
        if (isset($questions[$index]) && $questions[$index]['correct'] === $answer) {
            $correct++;
        }
    }
    
    return [
        'correct' => $correct,
        'total' => $total,
        'percentage' => $total > 0 ? round(($correct / $total) * 100) : 0
    ];
}

/**
 * Get score interpretation
 */
function getScoreInterpretation($percentage) {
    if ($percentage >= 90) {
        return [
            'title' => 'Perfect Match! 🌟',
            'message' => 'You think exactly like professionals in this field! This course seems like a natural fit for you.',
            'color' => 'success'
        ];
    } elseif ($percentage >= 70) {
        return [
            'title' => 'Great Fit! ✨',
            'message' => 'You have strong alignment with this field. You\'d likely enjoy and succeed in this course!',
            'color' => 'success'
        ];
    } elseif ($percentage >= 50) {
        return [
            'title' => 'Good Potential 👍',
            'message' => 'You have some alignment with this field. With interest and effort, you could do well!',
            'color' => 'warning'
        ];
    } else {
        return [
            'title' => 'Consider Exploring 🔍',
            'message' => 'This field might challenge some of your preferences. Consider if you\'re open to growing in these areas.',
            'color' => 'info'
        ];
    }
}

