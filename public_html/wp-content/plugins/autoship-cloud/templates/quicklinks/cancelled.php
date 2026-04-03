<?php
/**
 * QuickLink Cancelled Template (Shopify-aligned).
 *
 * Displays confirmation that a QuickLink action was cancelled.
 * This template can be overridden by copying it to:
 * yourtheme/autoship/quicklinks/cancelled.php
 *
 * @package Autoship
 * @since   3.2.0
 *
 * @var string      $message                The cancellation message.
 * @var string      $site_name              The site name.
 * @var string|null $action_name            The action name (e.g., "pause", "resume").
 * @var string|null $custom_logo_url        Custom logo URL from QPilot.
 * @var string|null $custom_stylesheet_url  Custom stylesheet URL from QPilot.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get custom branding variables.
$custom_logo_url       = isset( $custom_logo_url ) ? $custom_logo_url : null;
$custom_stylesheet_url = isset( $custom_stylesheet_url ) ? $custom_stylesheet_url : null;
$action_name           = isset( $action_name ) ? $action_name : __( 'requested', 'autoship' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <?php // translators: %s: site name. ?>
    <title><?php echo esc_html( sprintf( __( 'Action Cancelled - %s', 'autoship' ), $site_name ) ); ?></title>

    <?php // Custom stylesheet from API. ?>
    <?php if ( ! empty( $custom_stylesheet_url ) ) : ?>
        <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Standalone page without WordPress head. ?>
        <link rel="stylesheet" href="<?php echo esc_url( $custom_stylesheet_url ); ?>">
    <?php endif; ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            color: #1e293b;
            line-height: 1.6;
            padding: 1.25rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .cancelled-card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Content */
        .card-content {
            padding: 2rem;
            text-align: center;
        }

        /* Logo Section */
        .merchant-logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .merchant-logo {
            width: 200px;
            height: auto;
        }

        .merchant-name {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        .merchant-name-large {
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 600;
        }

        /* Message */
        .message-text {
            font-size: 1.25rem;
            color: #475569;
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }

        .message-text strong {
            color: #1e293b;
            font-weight: 600;
        }

        .message-subtext {
            font-size: 0.9375rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        /* Help Box */
        .help-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .help-box-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.875rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .help-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .help-box li {
            position: relative;
            padding-left: 1.25rem;
            margin: 0.5rem 0;
            font-size: 0.9375rem;
            color: #475569;
            line-height: 1.5;
        }

        .help-box li:before {
            content: "\2022";
            position: absolute;
            left: 0.5rem;
            color: #94a3b8;
        }

        .help-box li:last-child {
            margin-bottom: 0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .account-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.9375rem;
            transition: color 0.2s ease;
        }

        .account-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Powered By Footer */
        .powered-by {
            text-align: center;
            padding: 1.5rem 1rem;
            background: #ffffff;
        }

        .powered-by-text {
            font-size: 0.6875rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .powered-by-logo {
            display: inline-block;
        }

        .powered-by-logo a {
            display: inline-block;
        }

        .powered-by-logo svg {
            width: 100px;
            height: auto;
        }

        /* Mobile Responsive */
        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .card-content {
                padding: 1.5rem;
            }

            .merchant-logo-section {
                margin-bottom: 1.5rem;
            }

            .merchant-logo {
                width: 150px;
            }

            .merchant-name-large {
                font-size: 1.25rem;
            }

            .help-box {
                padding: 1.25rem;
            }

            .powered-by {
                padding: 1.25rem 1rem;
            }

            .powered-by-logo svg {
                width: 80px;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body>
<div class="cancelled-card">
    <div class="card-content">
        <!-- Store Logo/Name -->
        <div class="merchant-logo-section">
            <?php if ( ! empty( $custom_logo_url ) ) : ?>
                <img src="<?php echo esc_url( $custom_logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" class="merchant-logo">
                <div class="merchant-name"><?php echo esc_html( $site_name ); ?></div>
            <?php else : ?>
                <?php
                $theme_logo_id = get_theme_mod( 'custom_logo' );
                if ( $theme_logo_id ) :
                    $theme_logo_url = wp_get_attachment_image_url( $theme_logo_id, 'medium' );
                    ?>
                    <img src="<?php echo esc_url( $theme_logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" class="merchant-logo">
                    <div class="merchant-name"><?php echo esc_html( $site_name ); ?></div>
                <?php else : ?>
                    <div class="merchant-name-large"><?php echo esc_html( $site_name ); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Message -->
        <p class="message-text">
            <?php
            printf(
            /* translators: %s: action name (e.g., "pause", "resume") */
                    esc_html__( 'You have cancelled the %s action.', 'autoship' ),
                    '<strong>' . esc_html( strtolower( $action_name ) ) . '</strong>'
            );
            ?>
        </p>

        <p class="message-subtext">
            <?php esc_html_e( 'No changes have been made to your subscription.', 'autoship' ); ?>
        </p>

        <!-- Help Box -->
        <div class="help-box">
            <div class="help-box-header"><?php esc_html_e( 'What You Can Do', 'autoship' ); ?></div>
            <ul>
                <li><?php esc_html_e( 'Manage your subscription from your account dashboard', 'autoship' ); ?></li>
                <li><?php esc_html_e( 'Request a new action link if needed', 'autoship' ); ?></li>
                <li><?php esc_html_e( 'Contact our support team if you need assistance', 'autoship' ); ?></li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ( is_user_logged_in() && function_exists( 'wc_get_page_permalink' ) ) : ?>
                <a href="<?php echo esc_url( trailingslashit( wc_get_page_permalink( 'myaccount' ) ) . 'scheduled-orders' ); ?>" class="btn btn-primary">
                    <?php esc_html_e( 'Go to My Subscriptions', 'autoship' ); ?>
                </a>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="account-link">
                    <?php esc_html_e( 'Go to My Account', 'autoship' ); ?>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-primary">
                    <?php esc_html_e( 'Return to Home', 'autoship' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Powered By Footer -->
    <div class="powered-by">
        <div class="powered-by-text"><?php esc_html_e( 'Powered by', 'autoship' ); ?></div>
        <div class="powered-by-logo">
            <a href="https://www.autoship.cloud/" target="_blank" rel="noopener noreferrer">
                <svg xmlns="http://www.w3.org/2000/svg" width="164" height="32" viewBox="0 0 164 32" fill="#230C5A">
                    <path d="M3.6401 32C2.35203 32 1.24995 31.1678 0.610858 29.7109C-0.546626 27.0663 -0.141012 22.3012 2.53406 19.2148C4.1585 17.3408 6.51699 15.7997 9.25538 14.7311C11.052 7.15084 13.1592 0 18.167 0C23.1749 0 25.2821 7.15084 27.0786 14.7291C29.817 15.7976 32.1755 17.3387 33.8 19.2127C36.475 22.2991 36.8807 27.0643 35.7232 29.7089C35.0861 31.1678 33.984 32 32.6939 32C32.6781 32 32.6623 32 32.6464 32C28.5171 31.9342 26.9758 25.2827 25.1911 17.5833C25.1238 17.2894 25.0545 16.9935 24.9853 16.6956C22.8682 16.0236 20.5334 15.6517 18.167 15.6517C15.8006 15.6517 13.4659 16.0236 11.3487 16.6956C11.2795 16.9935 11.2102 17.2894 11.143 17.5833C9.35827 25.2848 7.81693 31.9363 3.68759 32C3.67176 32 3.65593 32 3.6401 32ZM27.8008 17.8319C29.0474 23.1909 30.5907 29.3904 32.6821 29.4232C32.684 29.4232 32.686 29.4232 32.69 29.4232C33.0461 29.4232 33.3053 28.9999 33.4616 28.6444C34.2827 26.7684 33.8653 23.1436 31.9539 20.9368C30.9013 19.7223 29.4728 18.6703 27.8008 17.8319ZM8.53319 17.8319C6.86127 18.6703 5.43272 19.7223 4.3801 20.9368C2.46877 23.1416 2.05128 26.7684 2.8724 28.6444C3.02871 28.9999 3.28791 29.4232 3.64406 29.4232C3.64604 29.4232 3.64802 29.4232 3.64999 29.4232C5.74138 29.3904 7.28469 23.1909 8.53121 17.8319H8.53319ZM18.167 2.57677C15.2644 2.57677 13.4441 8.15155 12.0314 13.8414C13.9941 13.3441 16.0737 13.0749 18.167 13.0749C20.2604 13.0749 22.3419 13.3441 24.3027 13.8414C22.888 8.15155 21.0696 2.57677 18.167 2.57677Z" fill="#230C5A"/>
                    <path d="M59.8938 24.0005H56.4609V21.9333C55.8653 22.6916 55.1293 23.2608 54.2547 23.6409C53.3802 24.019 52.4127 24.2101 51.3561 24.2101C49.8563 24.2101 48.5148 23.8711 47.3316 23.1909C46.1484 22.5128 45.2264 21.5532 44.5616 20.3141C43.8987 19.0751 43.5663 17.6387 43.5663 16.001C43.5663 14.3633 43.8987 12.9311 44.5616 11.7023C45.2244 10.4735 46.1484 9.52007 47.3316 8.84197C48.5148 8.16387 49.8563 7.82277 51.3561 7.82277C52.3553 7.82277 53.2753 8.0036 54.1103 8.36114C54.9453 8.71868 55.6734 9.25088 56.2888 9.94953V8.00154H59.8938V24.0005ZM55.0462 19.6278C55.9128 18.7093 56.3442 17.5011 56.3442 16.0031C56.3442 14.5051 55.9128 13.2969 55.0462 12.3783C54.1815 11.4598 53.0854 10.9996 51.7577 10.9996C50.4301 10.9996 49.3399 11.4598 48.4832 12.3783C47.6264 13.2969 47.199 14.5051 47.199 16.0031C47.199 17.5011 47.6264 18.7093 48.4832 19.6278C49.3379 20.5463 50.4301 21.0066 51.7577 21.0066C53.0854 21.0066 54.1815 20.5484 55.0462 19.6278Z" fill="#230C5A"/>
                    <path d="M77.9505 8.0036V24.0005H74.5176V21.9642C73.9399 22.6834 73.2197 23.2382 72.355 23.6265C71.4884 24.0149 70.5565 24.2101 69.5573 24.2101C67.4995 24.2101 65.879 23.6163 64.6958 22.4286C63.5126 21.2409 62.921 19.4778 62.921 17.1415V8.0036H66.526V16.6319C66.526 18.0703 66.8387 19.1429 67.4639 19.8518C68.0891 20.5607 68.9775 20.9162 70.133 20.9162C71.4211 20.9162 72.446 20.5011 73.2058 19.673C73.9656 18.8449 74.3455 17.6511 74.3455 16.0935V8.00565H77.9505V8.0036Z" fill="#230C5A"/>
                    <path d="M89.4601 23.1334C89.0366 23.493 88.5222 23.7622 87.9167 23.943C87.3113 24.1217 86.6722 24.2122 85.9975 24.2122C84.3058 24.2122 82.9979 23.7539 82.0739 22.8334C81.1499 21.9149 80.6889 20.5772 80.6889 18.8182V4.46927H84.2939V8.12483H88.4193V11.1208H84.2939V18.7299C84.2939 19.5086 84.4819 20.1025 84.8558 20.5114C85.2318 20.9203 85.7541 21.1258 86.4288 21.1258C87.2361 21.1258 87.9088 20.9059 88.447 20.4662L89.4561 23.1334H89.4601Z" fill="#230C5A"/>
                    <path d="M94.1335 23.1621C92.883 22.4635 91.9076 21.4895 91.2052 20.2422C90.5028 18.9949 90.1526 17.5812 90.1526 16.0031C90.1526 14.425 90.5028 13.0174 91.2052 11.7783C91.9076 10.5393 92.883 9.57144 94.1335 8.87279C95.384 8.17415 96.7868 7.82483 98.344 7.82483C99.9011 7.82483 101.334 8.17415 102.584 8.87279C103.833 9.57144 104.81 10.5413 105.512 11.7783C106.215 13.0174 106.565 14.425 106.565 16.0031C106.565 17.5812 106.215 18.9949 105.512 20.2422C104.81 21.4916 103.835 22.4635 102.584 23.1621C101.334 23.8608 99.9209 24.2101 98.344 24.2101C96.767 24.2101 95.382 23.8608 94.1335 23.1621ZM101.634 19.6278C102.501 18.7093 102.932 17.5011 102.932 16.0031C102.932 14.5051 102.501 13.2969 101.634 12.3783C100.77 11.4598 99.6736 10.9995 98.346 10.9995C97.0183 10.9995 95.9281 11.4598 95.0714 12.3783C94.2146 13.2969 93.7873 14.5051 93.7873 16.0031C93.7873 17.5011 94.2146 18.7093 95.0714 19.6278C95.9261 20.5463 97.0183 21.0066 98.346 21.0066C99.6736 21.0066 100.77 20.5484 101.634 19.6278Z" fill="#230C5A"/>
                    <path d="M110.69 23.7026C109.497 23.3635 108.546 22.9341 107.835 22.4142L109.22 19.5682C109.913 20.047 110.744 20.4333 111.715 20.721C112.687 21.0107 113.643 21.1546 114.586 21.1546C116.739 21.1546 117.817 20.5648 117.817 19.3874C117.817 18.8285 117.544 18.4381 116.996 18.2182C116.448 17.9983 115.568 17.7887 114.357 17.5894C113.089 17.3901 112.054 17.16 111.256 16.901C110.457 16.6421 109.766 16.188 109.179 15.5387C108.591 14.8894 108.298 13.9852 108.298 12.8284C108.298 11.3098 108.908 10.0975 110.131 9.18924C111.351 8.281 113.001 7.82688 115.077 7.82688C116.136 7.82688 117.192 7.95223 118.251 8.20086C119.307 8.45155 120.174 8.78444 120.847 9.20362L119.462 12.0496C118.154 11.2502 116.682 10.8516 115.047 10.8516C113.989 10.8516 113.187 11.016 112.639 11.3468C112.091 11.6756 111.816 12.1112 111.816 12.6496C111.816 13.2496 112.109 13.6729 112.697 13.9236C113.282 14.1743 114.193 14.4085 115.423 14.6284C116.654 14.8277 117.663 15.0579 118.453 15.3168C119.242 15.5757 119.919 16.0154 120.487 16.6339C121.054 17.2524 121.337 18.1319 121.337 19.2703C121.337 20.7683 120.712 21.9662 119.462 22.8662C118.211 23.7642 116.51 24.2142 114.355 24.2142C113.104 24.2142 111.884 24.0437 110.692 23.7046L110.69 23.7026Z" fill="#230C5A"/>
                    <path d="M136.567 9.59199C137.74 10.7715 138.326 12.5181 138.326 14.8339V24.0005H134.721V15.3127C134.721 13.9154 134.404 12.8612 133.769 12.1523C133.134 11.4434 132.23 11.0879 131.058 11.0879C129.733 11.0879 128.684 11.503 127.914 12.3311C127.145 13.1592 126.761 14.353 126.761 15.9106V23.9985H123.156V1.77333H126.761V9.86117C127.356 9.20157 128.092 8.69813 128.967 8.34881C129.841 7.99949 130.817 7.82483 131.895 7.82483C133.838 7.82483 135.395 8.41456 136.569 9.59199H136.567Z" fill="#230C5A"/>
                    <path d="M141.355 4.72407C140.912 4.29461 140.692 3.76035 140.692 3.1213C140.692 2.48224 140.914 1.94799 141.355 1.51853C141.796 1.08906 142.344 0.875361 142.999 0.875361C143.654 0.875361 144.2 1.08085 144.643 1.48976C145.085 1.89867 145.306 2.41444 145.306 3.03294C145.306 3.69254 145.09 4.2453 144.657 4.69531C144.224 5.14532 143.672 5.36929 142.999 5.36929C142.326 5.36929 141.796 5.15559 141.355 4.72613V4.72407ZM141.183 8.0036H144.788V24.0005H141.183V8.0036Z" fill="#230C5A"/>
                    <path d="M160.207 8.84197C161.39 9.52007 162.316 10.4797 162.991 11.7187C163.664 12.9578 164 14.3859 164 16.0031C164 17.6202 163.664 19.0545 162.991 20.3018C162.318 21.5511 161.39 22.5149 160.207 23.193C159.024 23.8731 157.682 24.2122 156.183 24.2122C154.105 24.2122 152.461 23.493 151.25 22.0546V29.8137H147.645V8.0036H151.078V10.1016C151.673 9.34335 152.409 8.77416 153.284 8.39402C154.158 8.01387 155.126 7.82483 156.183 7.82483C157.682 7.82483 159.024 8.16387 160.207 8.84403V8.84197ZM159.053 19.6278C159.908 18.7093 160.338 17.5011 160.338 16.0031C160.338 14.5051 159.91 13.2969 159.053 12.3783C158.197 11.4598 157.107 10.9995 155.779 10.9995C154.914 10.9995 154.135 11.205 153.442 11.6139C152.75 12.0229 152.202 12.6085 151.798 13.3667C151.394 14.125 151.193 15.0044 151.193 16.0031C151.193 17.0017 151.394 17.8812 151.798 18.6394C152.202 19.3977 152.75 19.9833 153.442 20.3922C154.135 20.8011 154.914 21.0066 155.779 21.0066C157.107 21.0066 158.197 20.5484 159.053 19.6278Z" fill="#230C5A"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
