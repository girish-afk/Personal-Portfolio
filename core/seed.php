<?php
/**
 * Initial Seed Data for Girish Pawar's Developer Portfolio
 * Contains authoritative initial information provided in project specification.
 */

return [
    'admin_user' => [
        'username' => 'admin',
        'email' => 'girish.pawar.dev@gmail.com',
        'password' => 'GirishPawar@2026!',
    ],
    'profile' => [
        'full_name' => 'Girish Pawar',
        'primary_title' => 'Web Developer',
        'headline_titles' => [
            'Web Developer',
            'Software Developer',
            'Full-Stack Developer',
            'AI Enthusiast',
            'Cybersecurity Enthusiast',
            'Computer Technology Student'
        ],
        'bio_short' => 'I am a Computer Technology professional with a strong interest in software development, web technologies, artificial intelligence, and cybersecurity. I enjoy building practical digital solutions, exploring new technologies, solving problems, and continuously improving my technical skills.',
        'bio_full' => "I am a Computer Technology professional with a strong interest in software development, web technologies, artificial intelligence, and cybersecurity.\n\nI enjoy building practical digital solutions, exploring new technologies, solving problems, and continuously improving my technical skills. My engineering philosophy blends structured design systems, clean code, relational database architecture, and a persistent curiosity for modern intelligent systems.",
        'avatar_url' => 'assets/img/girish-avatar.svg',
        'location' => 'Maharashtra, India',
        'email' => 'girish.pawar.dev@gmail.com',
        'phone' => '',
        'available_for_hire' => 1,
        'resume_url' => 'uploads/resume/Girish_Pawar_Resume.pdf',
        'approach_text' => 'I approach engineering with a focus on clean modular architecture, security-first mindset, and responsive user-centric design. Every project is crafted for real-world resilience, speed, and usability.',
        'learning_text' => 'Currently diving deeper into advanced AI agent integration, scalable micro-architectures, full-stack PHP/modern frameworks, and defensive security auditing.',
        'career_goals' => 'To engineer resilient, high-impact digital systems, solve challenging technical problems, and contribute meaningfully to innovative tech teams and cutting-edge products.'
    ],
    'skill_categories' => [
        ['name' => 'Programming', 'slug' => 'programming', 'sort_order' => 1],
        ['name' => 'Frontend', 'slug' => 'frontend', 'sort_order' => 2],
        ['name' => 'Backend', 'slug' => 'backend', 'sort_order' => 3],
        ['name' => 'Database', 'slug' => 'database', 'sort_order' => 4],
        ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'sort_order' => 5],
        ['name' => 'Tools & Workflow', 'slug' => 'tools', 'sort_order' => 6],
        ['name' => 'Core & Other', 'slug' => 'other', 'sort_order' => 7]
    ],
    'skills' => [
        // Programming
        ['category' => 'programming', 'name' => 'Java', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 85, 'icon_class' => 'fab fa-java', 'is_featured' => 1],
        ['category' => 'programming', 'name' => 'Python', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 88, 'icon_class' => 'fab fa-python', 'is_featured' => 1],
        ['category' => 'programming', 'name' => 'JavaScript', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 86, 'icon_class' => 'fab fa-js-square', 'is_featured' => 1],
        ['category' => 'programming', 'name' => 'PHP', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 84, 'icon_class' => 'fab fa-php', 'is_featured' => 1],

        // Frontend
        ['category' => 'frontend', 'name' => 'HTML5', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 95, 'icon_class' => 'fab fa-html5', 'is_featured' => 1],
        ['category' => 'frontend', 'name' => 'CSS3', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 90, 'icon_class' => 'fab fa-css3-alt', 'is_featured' => 1],
        ['category' => 'frontend', 'name' => 'Bootstrap 5', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 90, 'icon_class' => 'fab fa-bootstrap', 'is_featured' => 1],
        ['category' => 'frontend', 'name' => 'Responsive Web Design', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 92, 'icon_class' => 'fas fa-mobile-alt', 'is_featured' => 1],

        // Backend
        ['category' => 'backend', 'name' => 'PHP', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 84, 'icon_class' => 'fab fa-php', 'is_featured' => 1],
        ['category' => 'backend', 'name' => 'MySQL', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 86, 'icon_class' => 'fas fa-database', 'is_featured' => 1],
        ['category' => 'backend', 'name' => 'REST API Fundamentals', 'proficiency_level' => 'Working Knowledge', 'proficiency_percent' => 80, 'icon_class' => 'fas fa-network-wired', 'is_featured' => 1],

        // Database
        ['category' => 'database', 'name' => 'MySQL', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 86, 'icon_class' => 'fas fa-database', 'is_featured' => 1],
        ['category' => 'database', 'name' => 'SQL', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 88, 'icon_class' => 'fas fa-code', 'is_featured' => 1],
        ['category' => 'database', 'name' => 'DBMS', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 85, 'icon_class' => 'fas fa-server', 'is_featured' => 1],

        // Cybersecurity
        ['category' => 'cybersecurity', 'name' => 'Kali Linux', 'proficiency_level' => 'Working Knowledge', 'proficiency_percent' => 80, 'icon_class' => 'fab fa-linux', 'is_featured' => 1],
        ['category' => 'cybersecurity', 'name' => 'Networking Fundamentals', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 82, 'icon_class' => 'fas fa-project-diagram', 'is_featured' => 1],
        ['category' => 'cybersecurity', 'name' => 'Security Fundamentals', 'proficiency_level' => 'Working Knowledge', 'proficiency_percent' => 80, 'icon_class' => 'fas fa-shield-alt', 'is_featured' => 1],

        // Tools
        ['category' => 'tools', 'name' => 'Git', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 88, 'icon_class' => 'fab fa-git-alt', 'is_featured' => 1],
        ['category' => 'tools', 'name' => 'GitHub', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 88, 'icon_class' => 'fab fa-github', 'is_featured' => 1],
        ['category' => 'tools', 'name' => 'VS Code', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 94, 'icon_class' => 'fas fa-laptop-code', 'is_featured' => 1],
        ['category' => 'tools', 'name' => 'XAMPP', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 90, 'icon_class' => 'fas fa-server', 'is_featured' => 1],

        // Other
        ['category' => 'other', 'name' => 'AI Integration', 'proficiency_level' => 'Working Knowledge', 'proficiency_percent' => 78, 'icon_class' => 'fas fa-brain', 'is_featured' => 1],
        ['category' => 'other', 'name' => 'Problem Solving', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 90, 'icon_class' => 'fas fa-lightbulb', 'is_featured' => 1],
        ['category' => 'other', 'name' => 'UI/UX Design', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 85, 'icon_class' => 'fas fa-drafting-compass', 'is_featured' => 1],
        ['category' => 'other', 'name' => 'Communication', 'proficiency_level' => 'Proficient', 'proficiency_percent' => 88, 'icon_class' => 'fas fa-comments', 'is_featured' => 0],
        ['category' => 'other', 'name' => 'Teamwork & Collaboration', 'proficiency_level' => 'Advanced', 'proficiency_percent' => 92, 'icon_class' => 'fas fa-users', 'is_featured' => 0]
    ],
    'projects' => [
        [
            'title' => 'AgriShield',
            'slug' => 'agrishield',
            'category' => 'AI / Technology / Agriculture',
            'short_description' => 'A technology-based solution designed to support crop management and improve agricultural decision-making.',
            'cover_image' => 'assets/img/projects/agrishield.svg',
            'logo_url' => '',
            'tech_stack' => ['Python', 'Machine Learning', 'PHP', 'Bootstrap 5', 'MySQL'],
            'github_url' => '',
            'demo_url' => '',
            'status' => 'Completed',
            'development_date' => '2024',
            'key_features' => "• Intelligent crop health monitoring and early disease symptom detection\n• Actionable agricultural decision-making guidance tailored for farmers\n• Soil parameter evaluation and moisture trend analysis\n• Clean, responsive interface accessible across rural mobile devices",
            'challenges' => "Processing variable agronomic parameters and weather patterns into straightforward, rapid recommendations that do not overwhelm end-users.",
            'solutions' => "Engineered a lightweight prediction flow coupled with high-contrast, accessible UI cards designed for direct field usability.",
            'results' => "Demonstrated an intuitive crop advisory pipeline with responsive performance on both mobile and desktop screens.",
            'case_study_content' => "AgriShield was conceived to bridge the gap between complex agricultural science and everyday farming decisions. By pairing machine learning inference models with a clean web dashboard, farmers receive proactive alerts regarding potential disease vectors, irrigation advice, and crop health metrics without requiring deep technical knowledge.",
            'screenshots' => [],
            'is_featured' => 1,
            'is_visible' => 1,
            'sort_order' => 1
        ],
        [
            'title' => 'AURA AI',
            'slug' => 'aura-ai',
            'category' => 'Artificial Intelligence / Education',
            'short_description' => 'An AI-powered academic and university research assistant designed to help users access academic information and educational assistance.',
            'cover_image' => 'assets/img/projects/aura.svg',
            'logo_url' => '',
            'tech_stack' => ['Python', 'NLP', 'REST APIs', 'JavaScript', 'HTML5/CSS3'],
            'github_url' => '',
            'demo_url' => '',
            'status' => 'Active Development',
            'development_date' => '2024',
            'key_features' => "• Context-aware academic inquiry resolution and concept clarification\n• Research paper abstract summarization and key findings extraction\n• University curriculum navigation and subject syllabus indexing\n• Fast response latency with interactive conversational UI",
            'challenges' => "Maintaining precision in academic terminology while ensuring queries are answered in concise, student-friendly explanations.",
            'solutions' => "Built targeted prompt engineering frameworks with structured validation layers to reduce hallucination and emphasize source accuracy.",
            'results' => "Created a highly responsive educational companion capable of breaking down complex engineering concepts into digestible study notes.",
            'case_study_content' => "AURA AI functions as a digital academic mentor. Designed specifically for university students, it streamlines the literature review process, assists with curriculum review, and provides instant answers to technical questions across computer science and engineering disciplines.",
            'screenshots' => [],
            'is_featured' => 1,
            'is_visible' => 1,
            'sort_order' => 2
        ],
        [
            'title' => 'PROJECT ASTRO',
            'slug' => 'project-astro',
            'category' => 'Space Technology / NASA Space Apps Challenge 2026',
            'short_description' => 'An interactive Martian exploration and survival platform designed to help users explore Mars terrain, understand potential hazards, evaluate landing zones, and analyze mission risks.',
            'cover_image' => 'assets/img/projects/astro.svg',
            'logo_url' => '',
            'tech_stack' => ['JavaScript ES6+', 'HTML5 Canvas', 'CSS3', 'Telemetry Simulation', 'PHP'],
            'github_url' => '',
            'demo_url' => '',
            'status' => 'NASA Space Apps Challenge 2026',
            'development_date' => '2026',
            'key_features' => "• Interactive topographic Martian terrain visualization with hazard zones\n• Landing site viability assessment using multi-criteria risk scoring\n• Simulated atmospheric and radiation telemetry feedback\n• Mission critical checklist and resource survival calculator",
            'challenges' => "Transforming planetary terrain datasets and simulated hazard variables into an interactive, real-time visual canvas without lag.",
            'solutions' => "Leveraged hardware-accelerated HTML5 Canvas rendering alongside optimized geospatial vector calculations.",
            'results' => "Presented at the NASA Space Apps Challenge 2026, delivering an immersive exploration experience with zero rendering stutters.",
            'case_study_content' => "Developed as a technical submission for the NASA Space Apps Challenge 2026, PROJECT ASTRO allows mission planners and space enthusiasts to interactively inspect Martian landing candidates. By synthesizing slope grades, atmospheric densities, dust storm risks, and communications line-of-sight into a composite viability index, users can evaluate simulated landing scenarios.",
            'screenshots' => [],
            'is_featured' => 1,
            'is_visible' => 1,
            'sort_order' => 3
        ]
    ],
    'experience_claims' => [
        [
            'area' => 'UI/UX Design',
            'duration' => '2 Years',
            'description' => 'Focusing on clean typography, responsive wireframes, design systems, accessibility guidelines, and human-centered interfaces.',
            'sort_order' => 1
        ],
        [
            'area' => 'Full-Stack Development',
            'duration' => '1 Year',
            'description' => 'Building integrated web applications combining responsive client frontends with relational database persistence and server-side processing.',
            'sort_order' => 2
        ],
        [
            'area' => 'MySQL Database Development',
            'duration' => '1 Year',
            'description' => 'Designing relational database schemas, writing optimized SQL queries, enforcing data integrity, and integrating secure PDO transactions.',
            'sort_order' => 3
        ],
        [
            'area' => 'AI Integration',
            'duration' => '6 Months',
            'description' => 'Exploring machine learning pipelines, prompt engineering, NLP query processing, and integrating AI endpoints into functional web tools.',
            'sort_order' => 4
        ]
    ],
    'internships' => [
        [
            'company' => 'TechKing Solutions India',
            'location' => 'Shirdi',
            'domain' => 'Web Development',
            'technologies' => 'HTML, CSS, JavaScript, Bootstrap',
            'role_description' => 'Developed responsive user interfaces, implemented clean layout structures, and ensured cross-browser compatibility across commercial client projects.',
            'start_date' => '2024',
            'end_date' => '2024',
            'sort_order' => 1
        ],
        [
            'company' => 'Cyber Sanskar',
            'location' => 'Nashik',
            'domain' => 'Cybersecurity',
            'technologies' => 'Kali Linux, Networking Fundamentals',
            'role_description' => 'Conducted foundational security testing, analyzed network topologies, studied vulnerability scanning protocols, and implemented security best practices.',
            'start_date' => '2024',
            'end_date' => '2024',
            'sort_order' => 2
        ]
    ],
    'education' => [
        [
            'degree' => 'Diploma in Computer Technology',
            'institution' => 'Sanjivani K.B.P. Polytechnic College',
            'score' => 'Percentage: 87.03%',
            'duration' => 'August 2022 – June 2025',
            'description' => 'Rigorous academic curriculum covering programming fundamentals (Java, Python, C++), database management systems, operating systems, software engineering, and computer networking.',
            'sort_order' => 1
        ]
    ],
    'certifications' => [
        [
            'title' => 'IEEE Coding Challenge 2026',
            'certificate_type' => 'Certificate of Participation',
            'issuing_organization' => 'IEEE Student Branch – Sanjivani University',
            'issue_date' => '23 September 2026',
            'rank_achievement' => 'Reported Rank: 6th',
            'credential_id' => 'IEEE-SB-2026-CC-06',
            'verification_url' => '',
            'image_url' => 'assets/img/ieee-certificate.svg',
            'description' => 'Participated in a competitive university-wide algorithmic coding tournament, solving complex data structures and algorithmic challenges under timed conditions and securing 6th place.',
            'sort_order' => 1
        ]
    ],
    'social_links' => [
        [
            'platform' => 'GitHub',
            'display_label' => 'girish-afk',
            'url' => 'https://github.com/girish-afk',
            'icon_class' => 'fab fa-github',
            'is_visible' => 1,
            'sort_order' => 1
        ],
        [
            'platform' => 'Portfolio',
            'display_label' => 'girish-afk.github.io',
            'url' => 'https://girish-afk.github.io/Portfolio/',
            'icon_class' => 'fas fa-globe',
            'is_visible' => 1,
            'sort_order' => 2
        ],
        [
            'platform' => 'YouTube',
            'display_label' => 'Gojo Sensei Live',
            'url' => 'https://www.youtube.com',
            'icon_class' => 'fab fa-youtube',
            'is_visible' => 1,
            'sort_order' => 3
        ],
        [
            'platform' => 'Instagram',
            'display_label' => '@gojosenseilive101',
            'url' => 'https://instagram.com/gojosenseilive101',
            'icon_class' => 'fab fa-instagram',
            'is_visible' => 1,
            'sort_order' => 4
        ],
        [
            'platform' => 'LinkedIn',
            'display_label' => 'LinkedIn Profile',
            'url' => '',
            'icon_class' => 'fab fa-linkedin-in',
            'is_visible' => 0,
            'sort_order' => 5
        ]
    ],
    'settings' => [
        'site_title' => 'Girish Pawar — Personal Developer Portfolio',
        'meta_description' => 'Official portfolio of Girish Pawar: Web Developer, Software Developer, and Computer Technology Student specializing in clean frontend and backend architectures, AI, and cybersecurity.',
        'meta_keywords' => 'Girish Pawar, Web Developer, Full-Stack Developer, Computer Technology, AgriShield, AURA AI, PROJECT ASTRO, Portfolio',
        'primary_color' => '#06b6d4',
        'theme_default' => 'dark',
        'contact_email_notifications' => '1',
        'recaptcha_enabled' => '0'
    ]
];
