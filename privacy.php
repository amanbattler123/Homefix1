<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

$pageTitle = "Privacy Policy - HomeFix Pro";

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
                <h1 style="font-size:2rem;font-weight:700;color:#111827;margin-bottom:0.75rem;">Privacy Policy</h1>
                <p style="font-size:0.95rem;color:#6b7280;">This is a sample privacy policy for the HomeFix Pro demo application. The text below is placeholder content and does not represent a real legal document.</p>
                <p style="font-size:0.85rem;color:#9ca3af;margin-top:0.5rem;">Last updated: <?php echo date('F j, Y'); ?></p>
            </header>

            <main style="padding:1.5rem 2rem 2.5rem 2rem;font-size:0.95rem;color:#374151;line-height:1.7;">
                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">1. Information We Collect</h2>
                    <p>For demonstration purposes, HomeFix Pro may collect basic information such as your name, email address, phone number, and general service preferences. In a real production system, this section would describe all categories of data collected and how they are obtained.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">2. How We Use Your Information</h2>
                    <p>In this demo, collected information is used only to illustrate how a home services platform might match homeowners with technicians. Typical uses could include:</p>
                    <ul style="margin:0.5rem 0 0 1.25rem;list-style:disc;">
                        <li>Creating and managing your demo account;</li>
                        <li>Simulating service requests and technician responses;</li>
                        <li>Improving the example user experience within the application.</li>
                    </ul>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">3. Cookies and Tracking</h2>
                    <p>This demo may use simple session cookies to keep you signed in and to remember basic preferences. No advanced tracking or analytics are implemented beyond what is necessary to demonstrate the platform.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">4. Data Sharing</h2>
                    <p>For this demo, data is not shared with third parties. In a real deployment, this section would explain any sharing with payment providers, communication services, or other partners.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">5. Data Security</h2>
                    <p>While this is a sample project, we still aim to handle data in a careful way. In a production environment, this section would describe encryption, access controls, and other security practices.</p>
                </section>

                <section style="margin-bottom:1.75rem;">
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">6. Your Choices</h2>
                    <p>You may request to have your demo data removed or reset by contacting the project owner or administrator. In a live system, this would describe options like accessing, correcting, or deleting your information.</p>
                </section>

                <section>
                    <h2 style="font-size:1.15rem;font-weight:600;color:#111827;margin-bottom:0.5rem;">7. Contact</h2>
                    <p>If you have any questions about this sample privacy policy, you can reach us using the contact details shown on the Contact page of this demo application.</p>
                </section>
            </main>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
