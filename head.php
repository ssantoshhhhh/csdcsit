<!DOCTYPE html>
<html lang="en-US">
<head>
    <link rel="icon" href="logo-bg-rem.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SRKREC CSD & CSIT - Attendance Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Typography Standards -->
    <link rel="stylesheet" href="assets/css/typography-standards.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-blue: #076593;
            --secondary-blue: #0089E4;
            --light-blue: #f4f8fb;
            --white: #ffffff;
            --gray-light: #f8f9fa;
            --gray-medium: #6c757d;
            --gray-dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--white);
            color: var(--gray-dark);
            line-height: 1.5;
            font-size: 14px; /* Base font size */
        }
        
        .top-bar {
            background: var(--primary-blue);
            color: var(--white);
            padding: 6px 0;
            font-size: 12px;
        }
        
        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-bar .codes {
            display: flex;
            gap: 20px;
        }
        
        .top-bar .codes span {
            font-weight: 500;
        }
        
        .top-bar .contact-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
        }
        
        .top-bar .contact-link:hover {
            color: #ffd700;
        }
        
        .main-header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo-section img {
            height: 60px;
            width: auto;
        }
        
        .college-info h1 {
            color: var(--primary-blue);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .college-info p {
            color: var(--gray-medium);
            font-size: 12px;
            margin: 0;
        }
        
        .main-nav {
            background: var(--primary-blue);
            padding: 0;
        }
        
        .main-nav .navbar-nav .nav-link {
            color: var(--white) !important;
            font-weight: 500;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }
        
        .main-nav .navbar-nav .nav-link:hover {
            background: var(--secondary-blue);
            color: var(--white) !important;
        }
        
        .main-nav .navbar-nav .nav-link.active {
            background: var(--secondary-blue);
        }
        
        .page-title {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: var(--white);
            padding: 40px 0;
            text-align: center;
        }
        
        .page-title h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-title p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .main-content {
            padding: 40px 0;
            min-height: 60vh;
        }
        
        .footer {
            background: var(--gray-dark);
            color: var(--white);
            padding: 40px 0 20px;
        }
        
        .footer h5 {
            color: var(--white);
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: var(--white);
        }
        
        .footer-bottom {
            background: #1a1a1a;
            padding: 20px 0;
            text-align: center;
            border-top: 1px solid #333;
        }
        
        /* Standardized Font Sizes */
        .text-xs { font-size: 11px !important; }
        .text-sm { font-size: 12px !important; }
        .text-base { font-size: 14px !important; }
        .text-md { font-size: 16px !important; }
        .text-lg { font-size: 18px !important; }
        .text-xl { font-size: 20px !important; }
        .text-2xl { font-size: 24px !important; }
        .text-3xl { font-size: 28px !important; }
        
        /* Mobile Responsive Improvements */
        @media (max-width: 1200px) {
            .container {
                max-width: 100%;
                padding-left: 15px;
                padding-right: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .top-bar .codes {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .top-bar .container {
                flex-direction: column;
                gap: 10px;
            }
            
            .logo-section {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px 0;
            }
            
            .logo-section img {
                height: 50px;
                margin: 0 auto;
            }
            
            .college-info h1 {
                font-size: 18px;
                margin-bottom: 5px;
            }
            
            .college-info p {
                font-size: 11px;
            }
            
            .page-title {
                padding: 25px 0;
            }
            
            .page-title h2 {
                font-size: 20px;
                margin-bottom: 8px;
            }
            
            .page-title p {
                font-size: 13px;
            }
            
            .main-content {
                padding: 30px 0;
            }
            
            .main-nav .navbar-nav .nav-link {
                padding: 12px 15px;
                font-size: 14px;
                text-align: center;
            }
            
            .main-nav .navbar-toggler {
                border: none;
                padding: 4px 8px;
            }
            
            .main-nav .navbar-toggler:focus {
                box-shadow: none;
            }
            
            .main-nav .navbar-collapse {
                background: var(--primary-blue);
                margin-top: 10px;
                border-radius: 10px;
            }
            
            /* Card improvements for mobile */
            .card {
                margin-bottom: 20px;
            }
            
            .card-body {
                padding: 20px 15px;
            }
            
            .card-header {
                padding: 15px 20px;
            }
            
            /* Form improvements for mobile */
            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            /* Table improvements for mobile */
            .table-responsive {
                border-radius: 10px;
                overflow: hidden;
            }
            
            .table th,
            .table td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            /* Grid improvements for mobile */
            .row {
                margin-left: -10px;
                margin-right: -10px;
            }
            
            .col-md-6,
            .col-md-4,
            .col-md-3,
            .col-md-2 {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            /* Alert improvements for mobile */
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 10px;
            }
            
            /* Breadcrumb improvements for mobile */
            .breadcrumb {
                font-size: 13px;
                margin-bottom: 20px;
            }
            
            .breadcrumb-item + .breadcrumb-item::before {
                padding-left: 8px;
                padding-right: 8px;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .page-title h2 {
                font-size: 18px;
            }
            
            .page-title p {
                font-size: 12px;
            }
            
            .main-content {
                padding: 20px 0;
            }
            
            .card-body {
                padding: 15px 10px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 8px 6px;
                font-size: 11px;
            }
            
            .logo-section img {
                height: 40px;
            }
            
            .college-info h1 {
                font-size: 16px;
            }
            
            .college-info p {
                font-size: 10px;
            }
            
            .main-nav .navbar-nav .nav-link {
                padding: 10px 12px;
                font-size: 12px;
            }
        }
        
        /* Landscape orientation fixes */
        @media (max-width: 768px) and (orientation: landscape) {
            .page-title {
                padding: 20px 0;
            }
            
            .main-content {
                padding: 20px 0;
            }
        }
        
        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .logo-section img {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }
        
        /* Print styles */
        @media print {
            .main-nav,
            .top-bar,
            .footer {
                display: none !important;
            }
            
            .main-content {
                padding: 0;
            }
            
            .page-title {
                background: none !important;
                color: black !important;
                padding: 10px 0;
            }
        }
    </style>
</head> 