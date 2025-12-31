<?php
/**
 * BingeTV SEO Optimization
 * Comprehensive SEO features for search engine visibility
 */

class SEO
{
    private static $site_data = [
        'name' => 'BingeTV',
        'description' => 'Premium TV Streaming for Kenya - Watch Premier League, National Geographic, and 150+ channels in 4K quality',
        'keywords' => 'TV streaming Kenya, Premier League streaming, National Geographic Kenya, online TV Kenya, streaming service Kenya, BingeTV, live TV Kenya, sports streaming Kenya',
        'author' => 'BingeTV',
        'url' => 'https://bingetv.co.ke',
        'image' => 'https://bingetv.co.ke/assets/images/og-image.jpg',
        'twitter' => '@BingeTVKenya',
        'facebook' => 'BingeTVKenya'
    ];

    /**
     * Generate page-specific meta tags
     */
    public static function getMetaTags($page = 'home', $data = [])
    {
        $meta = self::$site_data;

        switch ($page) {
            case 'home':
                $meta['title'] = 'BingeTV - Premium TV Streaming for Kenya | Premier League & Sports';
                $meta['description'] = 'Never miss Premier League & Premium Sports. Watch 150+ channels including National Geographic, ESPN, and more in 4K quality. M-PESA payments accepted.';
                $meta['keywords'] = 'TV streaming Kenya, Premier League streaming, National Geographic Kenya, online TV Kenya, streaming service Kenya, BingeTV, live TV Kenya, sports streaming Kenya, 4K TV Kenya';
                break;

            case 'channels':
                $meta['title'] = 'TV Channels - BingeTV Kenya | 150+ Premium Channels';
                $meta['description'] = 'Watch 150+ premium TV channels including Premier League, National Geographic, ESPN, BBC, CNN, and more. HD and 4K quality streaming in Kenya.';
                $meta['keywords'] = 'TV channels Kenya, Premier League channels, National Geographic Kenya, ESPN Kenya, BBC Kenya, CNN Kenya, HD channels Kenya, 4K channels Kenya';
                break;

            case 'gallery':
                $meta['title'] = 'Video Gallery - BingeTV Kenya | High-Quality Sports & Documentaries';
                $meta['description'] = 'Explore our video gallery featuring Premier League highlights, National Geographic documentaries, and premium sports content in high resolution.';
                $meta['keywords'] = 'video gallery Kenya, Premier League highlights, National Geographic videos, sports videos Kenya, documentary streaming Kenya, high resolution videos';
                break;

            case 'subscribe':
                $meta['title'] = 'Subscribe to BingeTV - Premium TV Streaming Plans | Kenya';
                $meta['description'] = 'Choose from our flexible subscription plans. Sports Starter, Sports Pro, and Sports Elite packages. M-PESA payments accepted. Start your free trial today!';
                $meta['keywords'] = 'BingeTV subscription, TV streaming plans Kenya, M-PESA payment, sports streaming subscription, premium TV Kenya, free trial Kenya';
                break;

            case 'login':
                $meta['title'] = 'Login - BingeTV Kenya | Access Your Account';
                $meta['description'] = 'Login to your BingeTV account to access premium TV streaming, manage your subscription, and enjoy uninterrupted entertainment.';
                break;

            case 'register':
                $meta['title'] = 'Sign Up - BingeTV Kenya | Create Your Account';
                $meta['description'] = 'Create your BingeTV account and start streaming premium TV channels. Quick registration with M-PESA payment support.';
                break;

            case 'forgot-password':
                $meta['title'] = 'Reset Password - BingeTV Kenya';
                $meta['description'] = 'Reset your BingeTV account password.';
                break;

            case 'packages':
                $meta['title'] = 'Choose Your Plan - BingeTV Kenya | Premium TV Streaming Packages';
                $meta['description'] = 'Select the perfect BingeTV package for your streaming needs. Flexible plans with M-PESA payment. Start your premium TV experience today.';
                $meta['keywords'] = 'TV streaming packages Kenya, BingeTV plans, premium TV subscription, M-PESA payment, streaming service plans Kenya';
                break;

            case 'support':
                $meta['title'] = 'Support - BingeTV Kenya | 24/7 Customer Service';
                $meta['description'] = 'Get help with BingeTV streaming service. 24/7 support via WhatsApp, email, phone, and live chat. Technical assistance and customer service.';
                $meta['keywords'] = 'BingeTV support, customer service, technical help, streaming assistance, 24/7 support Kenya';
                break;

            case 'help':
                $meta['title'] = 'Help Center - BingeTV Kenya | FAQ & Support';
                $meta['description'] = 'Find answers to common questions about BingeTV streaming service. Comprehensive FAQ and troubleshooting guides.';
                $meta['keywords'] = 'BingeTV help, FAQ, troubleshooting, streaming guide, support center';
                break;

            case 'privacy':
                $meta['title'] = 'Privacy Policy - BingeTV Kenya | Data Protection';
                $meta['description'] = 'Learn how BingeTV protects your privacy and personal data. Our commitment to data security and user privacy.';
                $meta['keywords'] = 'BingeTV privacy policy, data protection, user privacy, personal information security';
                break;

            case 'terms':
                $meta['title'] = 'Terms of Service - BingeTV Kenya | User Agreement';
                $meta['description'] = 'Read BingeTV terms of service and user agreement. Understand your rights and responsibilities as a subscriber.';
                $meta['keywords'] = 'BingeTV terms of service, user agreement, subscription terms, legal terms';
                break;
        }

        // Merge with custom data
        if (!empty($data)) {
            $meta = array_merge($meta, $data);
        }

        return $meta;
    }

    /**
     * Generate structured data (JSON-LD)
     */
    public static function getStructuredData($page = 'home', $data = [])
    {
        $base_data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'BingeTV',
            'url' => 'https://bingetv.co.ke',
            'logo' => 'https://bingetv.co.ke/assets/images/logo.png',
            'description' => 'Premium TV Streaming Service for Kenya',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'KE',
                'addressRegion' => 'Nairobi'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+254-700-000-000',
                'contactType' => 'customer service',
                'email' => 'support@bingetv.co.ke'
            ],
            'sameAs' => [
                'https://facebook.com/BingeTVKenya',
                'https://twitter.com/BingeTVKenya',
                'https://instagram.com/BingeTVKenya'
            ]
        ];

        switch ($page) {
            case 'home':
                $base_data['@type'] = 'WebSite';
                $base_data['potentialAction'] = [
                    '@type' => 'SearchAction',
                    'target' => 'https://bingetv.co.ke/search?q={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ];
                break;

            case 'channels':
                $base_data['@type'] = 'ItemList';
                $base_data['name'] = 'TV Channels - BingeTV Kenya';
                $base_data['description'] = 'Premium TV channels available on BingeTV';
                break;

            case 'subscribe':
                $base_data['@type'] = 'Product';
                $base_data['name'] = 'BingeTV Subscription Plans';
                $base_data['description'] = 'Premium TV streaming subscription plans for Kenya';
                break;
        }

        return json_encode($base_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate Open Graph tags
     */
    public static function getOpenGraphTags($page = 'home', $data = [])
    {
        $meta = self::getMetaTags($page, $data);

        return [
            'og:title' => $meta['title'],
            'og:description' => $meta['description'],
            'og:url' => $meta['url'] . $_SERVER['REQUEST_URI'],
            'og:type' => 'website',
            'og:image' => $meta['image'],
            'og:site_name' => $meta['name'],
            'og:locale' => 'en_KE',
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $meta['title'],
            'twitter:description' => $meta['description'],
            'twitter:image' => $meta['image'],
            'twitter:site' => $meta['twitter']
        ];
    }

    /**
     * Generate canonical URL
     */
    public static function getCanonicalUrl($page = 'home')
    {
        $base_url = 'https://bingetv.co.ke';

        switch ($page) {
            case 'home':
                return $base_url . '/';
            case 'channels':
                return $base_url . '/channels.php';
            case 'gallery':
                return $base_url . '/gallery.php';
            case 'subscribe':
                return $base_url . '/subscribe.php';
            case 'login':
                return $base_url . '/login.php';
            case 'register':
                return $base_url . '/register.php';
            case 'packages':
                return $base_url . '/packages.php';
            case 'support':
                return $base_url . '/support.php';
            case 'help':
                return $base_url . '/help.php';
            case 'privacy':
                return $base_url . '/privacy.php';
            case 'terms':
                return $base_url . '/terms.php';
            default:
                return $base_url . $_SERVER['REQUEST_URI'];
        }
    }

    /**
     * Generate breadcrumb structured data
     */
    public static function getBreadcrumbData($items = [])
    {
        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        $position = 1;
        foreach ($items as $item) {
            $breadcrumb['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $item['name'],
                'item' => $item['url']
            ];
            $position++;
        }

        return json_encode($breadcrumb, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate FAQ structured data
     */
    public static function getFAQData($faqs = [])
    {
        if (empty($faqs)) {
            $faqs = [
                [
                    'question' => 'What is BingeTV?',
                    'answer' => 'BingeTV is Kenya\'s premier TV streaming service offering 150+ channels including Premier League, National Geographic, ESPN, and more in 4K quality.'
                ],
                [
                    'question' => 'How much does BingeTV cost?',
                    'answer' => 'BingeTV offers flexible subscription plans starting from KES 500/month for Sports Starter, KES 1,000/month for Sports Pro, and KES 1,500/month for Sports Elite.'
                ],
                [
                    'question' => 'What payment methods do you accept?',
                    'answer' => 'We accept M-PESA payments for all subscriptions. You can pay using your M-PESA mobile money account for convenient and secure transactions.'
                ],
                [
                    'question' => 'Can I watch on multiple devices?',
                    'answer' => 'Yes! Depending on your plan, you can stream on 1-4 devices simultaneously. Sports Elite allows up to 4 devices at once.'
                ],
                [
                    'question' => 'Do you offer a free trial?',
                    'answer' => 'Yes, we offer a 7-day free trial for new users. No credit card required, just sign up and start streaming immediately.'
                ]
            ];
        }

        $faq_data = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => []
        ];

        foreach ($faqs as $faq) {
            $faq_data['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }

        return json_encode($faq_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate sitemap data
     */
    public static function getSitemapData()
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sitemap = [
            [
                'url' => 'https://bingetv.co.ke/',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '1.0'
            ],
            [
                'url' => 'https://bingetv.co.ke/channels.php',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9'
            ],
            [
                'url' => 'https://bingetv.co.ke/gallery.php',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ],
            [
                'url' => 'https://bingetv.co.ke/subscribe.php',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.9'
            ],
            [
                'url' => 'https://bingetv.co.ke/login.php',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => 'https://bingetv.co.ke/register.php',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ]
        ];

        // Add channel pages
        try {
            $channelsQuery = "SELECT id, name FROM channels WHERE is_active = true ORDER BY sort_order";
            $channelsStmt = $conn->prepare($channelsQuery);
            $channelsStmt->execute();
            $channels = $channelsStmt->fetchAll();

            foreach ($channels as $channel) {
                $sitemap[] = [
                    'url' => 'https://bingetv.co.ke/channel.php?id=' . $channel['id'],
                    'lastmod' => date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.6'
                ];
            }
        } catch (Exception $e) {
            // Continue without channel pages if error
        }

        return $sitemap;
    }

    /**
     * Generate robots.txt content
     */
    public static function getRobotsTxt()
    {
        return "User-agent: *
Allow: /

# Sitemaps
Sitemap: https://bingetv.co.ke/sitemap.xml
Sitemap: https://bingetv.co.ke/sitemap-images.xml

# Disallow admin areas
Disallow: /admin/
Disallow: /api/
Disallow: /includes/
Disallow: /config/
Disallow: /cache/
Disallow: /logs/

# Allow important pages
Allow: /channels.php
Allow: /gallery.php
Allow: /subscribe.php
Allow: /login.php
Allow: /register.php

# Crawl delay
Crawl-delay: 1";
    }
}
?>