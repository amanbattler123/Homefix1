<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

$pageTitle = "Terms of Service - HomeFix Pro";

$db = new Database();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="page-wrapper" style="min-height:100vh;background:#f9fafb;font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
        <div class="policy-container" style="max-width:900px;margin:4rem auto;padding:0 1.5rem;background:#ffffff;border-radius:0.75rem;box-shadow:0 10px 25px rgba(15,23,42,0.08);border:1px solid #e5e7eb;">
            <header style="padding:2.5rem 2rem 1.5rem 2rem;border-bottom:1px solid #e5e7eb;">
                <h1 style="font-size:2rem;font-weight:700;color:#111827;margin-bottom:0.75rem;">Terms of Service</h1>
                <p style="font-size:0.95rem;color:#6b7280;">These sample terms of service describe how users may interact with the HomeFix Pro demo platform. The text below is placeholder content and not a real legal agreement.</p>
                <p style="font-size:0.85rem;color:#9ca3af;margin-top:0.5rem;">Last updated: <?php echo date('F j, Y'); ?></p>
            </header>

            <main style="padding:1.5rem 2rem 2.5rem 2rem;font-size:0.95rem;color:#374151;line-height:1.7;">
                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">1. Acceptance of Terms</h2>
                    <p>By accessing or using this demo of HomeFix Pro, you agree that these sample terms apply to your use of the application. In a real production system, this section would explain the legal relationship between you and the service provider.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">2. Use of the Service</h2>
                    <p>This platform is intended only for demonstration and testing. You agree not to upload real sensitive personal data or rely on the platform for real-world service arrangements.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">3. User Accounts</h2>
                    <p>You may be able to create a basic account as a homeowner or technician. Account information is used solely for demo purposes. In a production system, this section would cover account security, credentials, and responsibilities.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">4. No Warranties</h2>
                    <p>This demo is provided "as is" without any guarantees. It may contain bugs, incomplete features, or placeholder data. Real-world performance and reliability are not represented here.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">5. Limitation of Liability</h2>
                    <p>Because this is a demonstration project, the creators are not responsible for any damages, losses, or issues arising from your use of the demo environment.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">6. Changes to These Terms</h2>
                    <p>These sample terms may be updated or replaced as the project evolves. Any significant changes to the demo will typically be reflected on this page.</p>
                </section>

                <section>
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">7. Contact</h2>
                    <p>If you have questions about these sample terms, please use the contact details listed on the Contact page of this demo application.</p>
                </section>
            </main>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
