<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - indEx</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --maroon-primary: #7b2d26;
            --maroon-light: #a74c42;
            --maroon-dark: #5a1f1a;
            --maroon-ultra-light: #f9efef;
            --gold-accent: #f5a623;
            --bg-gradient: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            --text-primary: #333333;
            --text-secondary: #777777;
            --border-color: #e0e0e0;
            --white: #ffffff;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.5;
            font-size: 16px;
            letter-spacing: 0.3px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--maroon-dark) 0%, var(--maroon-primary) 50%, var(--maroon-light) 100%);
            padding: 80px 20px 100px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
        }

        .heroContent {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .backButton {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 40px;
            transition: all 0.3s ease;
        }

        .backButton:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        h1 {
            font-size: 36px;
            font-weight: 800;
            color: white;
            text-align: center;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
            line-height: 1.4;
        }

        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* Main Content */
        .mainContainer {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        /* Category Tabs */
        .categoryTabs {
            display: flex;
            gap: 12px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .categoryTab {
            padding: 12px 24px;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .categoryTab:hover {
            border-color: var(--maroon-primary);
            color: var(--maroon-primary);
        }

        .categoryTab.active {
            background: var(--maroon-primary);
            border-color: var(--maroon-primary);
            color: white;
        }

        /* FAQ Section */
        .faqSection {
            margin-bottom: 48px;
        }

        .faqSection.hidden {
            display: none;
        }

        .sectionHeader {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .sectionIcon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .sectionTitle {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: 0.4px;
            line-height: 1.4;
        }

        .faqItems {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .faqItem {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .faqItem:hover {
            border-color: var(--maroon-light);
            box-shadow: 0 8px 24px rgba(123, 45, 38, 0.08);
        }

        .faqItem[open] {
            border-color: var(--maroon-primary);
            box-shadow: 0 12px 32px rgba(123, 45, 38, 0.12);
        }

        .faqQuestion {
            padding: 20px 24px;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            transition: all 0.3s ease;
            user-select: none;
            line-height: 1.5;
        }

        .faqQuestion::-webkit-details-marker {
            display: none;
        }

        .questionText {
            flex: 1;
        }

        .faqItem[open] .faqQuestion {
            color: var(--maroon-primary);
        }

        .faqQuestion i {
            color: var(--maroon-primary);
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .faqItem[open] .faqQuestion i {
            transform: rotate(180deg);
        }

        .faqAnswer {
            padding: 0 24px 20px 24px;
            font-size: 16px;
            line-height: 1.5;
            color: var(--text-secondary);
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Contact Banner */
        .contactBanner {
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            color: white;
            box-shadow: 0 20px 60px rgba(123, 45, 38, 0.25);
            position: relative;
            overflow: hidden;
        }

        .contactBanner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .contactBanner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .bannerContent {
            position: relative;
            z-index: 1;
        }

        .bannerIcon {
            font-size: 48px;
            margin-bottom: 24px;
            opacity: 0.9;
        }

        .bannerTitle {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: 0.4px;
            line-height: 1.4;
        }

        .bannerText {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 32px;
            opacity: 0.95;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .contactButton {
            padding: 16px 40px;
            background: white;
            color: var(--maroon-primary);
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.4px;
        }

        .contactButton:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
        }

        .contactButton:active {
            transform: translateY(-1px);
        }

        .contactButton:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 60px 20px 80px;
            }

            h1 {
                font-size: 32px;
            }

            .subtitle {
                font-size: 14px;
            }

            .mainContainer {
                padding: 40px 16px 60px;
            }

            .categoryTabs {
                margin-bottom: 32px;
            }

            .categoryTab {
                padding: 10px 20px;
                font-size: 14px;
            }

            .sectionIcon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .sectionTitle {
                font-size: 22px;
            }

            .faqQuestion {
                padding: 18px 20px;
                font-size: 16px;
            }

            .faqAnswer {
                padding: 0 20px 18px 20px;
                font-size: 14px;
            }

            .contactBanner {
                padding: 40px 24px;
                border-radius: 20px;
            }

            .bannerIcon {
                font-size: 40px;
            }

            .bannerTitle {
                font-size: 20px;
            }

            .bannerText {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="hero">
        <div class="heroContent">
            <button class="backButton" type="button" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </button>
            <h1>Frequently Asked Questions</h1>
            <p class="subtitle">Find answers to common questions about indEx</p>
        </div>
    </div>


    <div class="mainContainer">
        <div class="categoryTabs">
            <button class="categoryTab active" data-category="all">All</button>
            <button class="categoryTab" data-category="getting-started">Getting Started</button>
            <button class="categoryTab" data-category="account">Account & Security</button>
            <button class="categoryTab" data-category="support">Technical Support</button>
        </div>

        <section class="faqSection" data-category="getting-started">
            <div class="sectionHeader">
                <div class="sectionIcon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h2 class="sectionTitle">Getting Started</h2>
            </div>
            <div class="faqItems">
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">What is indEx and how does it work?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">indEx is a dedicated management system designed to streamline academic
                        processes for both teachers and students. It provides tools for managing classes, tracking grades,
                        recording attendance, and facilitating communication between educators and learners. The platform
                        offers a centralized dashboard where all academic activities can be monitored and managed efficiently.</p>
                </details>
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">How do I get started with indEx?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">To get started, simply register for an account by providing your email address
                        and creating a secure password. Students can join classes using class codes provided by their teachers,
                        while teachers can create and manage their own classes. After registration, you'll have access to your
                        personalized dashboard where you can explore all features.</p>
                </details>
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">Which devices does indEx support?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">indEx is fully responsive and works seamlessly across all devices including desktop
                        computers, laptops, tablets, and smartphones. You can access the platform through any modern web browser
                        such as Chrome, Firefox, Safari, or Edge. No additional software installation is required.</p>
                </details>
            </div>
        </section>

        <section class="faqSection" data-category="account">
            <div class="sectionHeader">
                <div class="sectionIcon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 class="sectionTitle">Account & Security</h2>
            </div>
            <div class="faqItems">
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">Is my data secure?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">Yes, absolutely. All passwords are encrypted using industry-standard encryption
                        protocols. Your personal information is stored securely and is never shared with third parties. We
                        implement multiple layers of security including SSL/TLS encryption for data transmission and regular
                        security audits to protect your information.</p>
                </details>
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">Do I need an account to use indEx?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">Yes, both students and teachers need to create an account to access indEx features.
                        This ensures that your academic data is personalized and secure. Registration is quick and free - you'll
                        only need to verify your email address to get started.</p>
                </details>
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">Can I back up my data?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">The system automatically backs up all data regularly to secure servers. Additionally,
                        teachers can export class data, grades, and attendance records in various formats (CSV, Excel) for their
                        own records. Students can also download their grade reports and attendance summaries.</p>
                </details>
            </div>
        </section>

        <section class="faqSection" data-category="support">
            <div class="sectionHeader">
                <div class="sectionIcon">
                    <i class="fas fa-headset"></i>
                </div>
                <h2 class="sectionTitle">Technical Support</h2>
            </div>
            <div class="faqItems">
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">I lost my data, can I recover it?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">Please contact the system administrator immediately if you believe data has been lost.
                        Our automated backup system maintains regular snapshots of all data, which can be used for recovery in
                        most cases. Provide as much detail as possible about what data was lost and when you last saw it.</p>
                </details>
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">The site isn't loading properly.</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">Try clearing your browser cache and cookies first. If that doesn't work, try using
                        a different browser or device. Ensure you have a stable internet connection. If problems persist, please
                        contact technical support with details about your browser version, device type, and any error messages
                        you're seeing.</p>
                </details>
                <details class="faqItem">
                    <summary class="faqQuestion">
                        <span class="questionText">How long does it take to get a response from support?</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="faqAnswer">We aim to respond to all support requests within 24-48 hours during business days.
                        Urgent issues are prioritized and may receive faster responses. For immediate assistance with critical
                        issues, please mark your request as urgent in the subject line.</p>
                </details>
            </div>
        </section>

        <div class="contactBanner">
            <div class="bannerContent">
                <div class="bannerIcon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="bannerTitle">Still have questions?</h3>
                <p class="bannerText">Our support team is here to help. Get in touch with us and we will respond as soon as possible</p>
                <button class="contactButton" disabled>
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </button>
            </div>
        </div>
    </div>

    <script>
        // Category filtering
        const categoryTabs = document.querySelectorAll('.categoryTab');
        const faqSections = document.querySelectorAll('.faqSection');

        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                categoryTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const activeCategory = tab.getAttribute('data-category');

                if (activeCategory === 'all') {
                    faqSections.forEach(section => {
                        section.style.display = 'block';
                    });
                } else {
                    faqSections.forEach(section => {
                        if (section.getAttribute('data-category') === activeCategory) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>