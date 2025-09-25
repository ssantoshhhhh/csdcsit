# SRKR CSD & CSIT Typography Style Guide

## Overview
This style guide provides consistent font styles and sizes across all pages, based on the design patterns from your placements.php page.

## Quick Reference

### Font Sizes & Weights
```css
/* Hero Titles (like "Placement Gallery") */
.hero-title, .page-hero-title
font-size: 4rem (64px)
font-weight: 900 (black)
line-height: 1.1

/* Main Section Headings */
.section-title-large, h1.large
font-size: 2.8rem (44.8px)
font-weight: 800 (extrabold)
line-height: 1.1

/* Secondary Headings */
.section-title, h2.section-heading
font-size: 2.2rem (35.2px)
font-weight: 700 (bold)
line-height: 1.2

/* Sub-headings */
.sub-heading, h3.sub-title
font-size: 1.8rem (28.8px)
font-weight: 600 (semibold)
line-height: 1.2

/* Card Titles */
.card-title, h4.card-heading
font-size: 1.5rem (24px)
font-weight: 600 (semibold)
line-height: 1.2

/* Small Headings */
.small-heading, h5.minor-title
font-size: 1.25rem (20px)
font-weight: 500 (medium)
line-height: 1.2

/* Body Text */
.text-large, .lead-text
font-size: 1.2rem (19.2px)
font-weight: 400 (regular)
line-height: 1.6

/* Default Body */
.text-body, p
font-size: 1rem (16px)
font-weight: 400 (regular)
line-height: 1.5

/* Small Text */
.text-small
font-size: 0.9rem (14.4px)
font-weight: 400 (regular)
line-height: 1.5

/* Captions */
.text-caption
font-size: 0.8rem (12.8px)
font-weight: 400 (regular)
line-height: 1.5
```

## How to Apply These Styles

### 1. Page Headers (like in placements.php)

```html
<!-- Use this pattern for main page sections -->
<div class="page-header">
    <h2 class="section-title-large">
        Your Main <span class="text-highlight">Heading</span>
    </h2>
    <p class="section-description">
        Your descriptive text that explains the section content
    </p>
</div>
```

**Result:** Large, bold heading (2.8rem, 800 weight) with smaller descriptive text below.

### 2. Section Headers

```html
<!-- For regular section headings -->
<h2 class="section-title">Section Heading</h2>
<p class="text-large">Large introductory text</p>

<!-- For card titles -->
<h4 class="card-title">Card Title</h4>
<p class="text-body">Regular body text</p>
```

### 3. Content Cards

```html
<div class="card">
    <div class="card-header">
        <h4 class="card-header-title">Card Header</h4>
        <p class="card-subtitle">Optional subtitle</p>
    </div>
    <div class="card-body">
        <p class="card-text">Card content text</p>
    </div>
</div>
```

## Examples for Different Page Types

### 1. About Page
```html
<div class="page-header">
    <h1 class="section-title-large">About <span class="text-highlight">Our Department</span></h1>
    <p class="section-description">Discover our mission, vision, and commitment to excellence</p>
</div>

<h2 class="section-title">Our Mission</h2>
<p class="text-large">Large introductory paragraph...</p>
<p class="text-body">Regular body text continues here...</p>
```

### 2. Courses Page
```html
<div class="page-header">
    <h1 class="section-title-large">Our <span class="text-highlight">Programs</span></h1>
    <p class="section-description">Comprehensive courses designed for future technology leaders</p>
</div>

<div class="course-section">
    <h2 class="section-title">B.Tech Computer Science</h2>
    <h3 class="sub-heading">Course Overview</h3>
    <p class="text-body">Course description...</p>
</div>
```

### 3. Faculty Page
```html
<div class="page-header">
    <h1 class="section-title-large">Meet Our <span class="text-highlight">Faculty</span></h1>
    <p class="section-description">Experienced educators shaping the next generation</p>
</div>

<div class="faculty-card">
    <h4 class="card-title">Dr. Faculty Name</h4>
    <h5 class="small-heading">Professor & Head</h5>
    <p class="text-small">Specialization area</p>
    <p class="text-body">Faculty description...</p>
</div>
```

### 4. Events Page
```html
<div class="page-header">
    <h1 class="section-title-large">Latest <span class="text-highlight">Events</span></h1>
    <p class="section-description">Stay updated with our department activities</p>
</div>

<div class="event-item">
    <h3 class="sub-heading">Event Title</h3>
    <p class="text-small text-light">Event date and time</p>
    <p class="text-body">Event description...</p>
</div>
```

### 5. Student Dashboard
```html
<div class="dashboard-header">
    <h1 class="section-title-large">Welcome, <span class="text-highlight">Student Name</span></h1>
    <p class="section-description">Your academic dashboard and progress</p>
</div>

<div class="stats-card">
    <h4 class="card-title">Attendance Summary</h4>
    <p class="text-caption">Current semester</p>
    <div class="stat-number">95%</div>
    <p class="text-small">Overall attendance</p>
</div>
```

## Utility Classes Available

### Font Weights
- `.font-weight-light` (300)
- `.font-weight-regular` (400)
- `.font-weight-medium` (500)
- `.font-weight-semibold` (600)
- `.font-weight-bold` (700)
- `.font-weight-extrabold` (800)
- `.font-weight-black` (900)

### Text Colors
- `.text-primary` (dark text)
- `.text-secondary` (medium gray)
- `.text-light` (light gray)
- `.text-accent` (blue accent)
- `.text-success` (green)
- `.text-warning` (orange)
- `.text-error` (red)

### Line Heights
- `.line-height-tight` (1.1)
- `.line-height-snug` (1.2)
- `.line-height-normal` (1.5)
- `.line-height-relaxed` (1.6)
- `.line-height-loose` (1.8)

## Responsive Behavior

The typography automatically adjusts for different screen sizes:

- **Desktop (1200px+)**: Full sizes as specified
- **Tablet (768px-1199px)**: Slightly reduced sizes
- **Mobile (576px-767px)**: Significantly reduced for readability
- **Small Mobile (<576px)**: Optimized for smallest screens

## Migration Tips

### For Existing Pages:

1. **Replace inline styles** with classes:
   ```html
   <!-- Instead of -->
   <h2 style="font-size: 2.8rem; font-weight: 800;">Title</h2>
   
   <!-- Use -->
   <h2 class="section-title-large">Title</h2>
   ```

2. **Use consistent patterns**:
   ```html
   <!-- Page header pattern -->
   <div class="page-header">
       <h1 class="section-title-large">Main Title</h1>
       <p class="section-description">Description</p>
   </div>
   ```

3. **Apply semantic classes**:
   ```html
   <!-- For cards -->
   <h4 class="card-title">Card Title</h4>
   <p class="card-text">Card content</p>
   
   <!-- For sections -->
   <h2 class="section-title">Section Title</h2>
   <p class="text-body">Section content</p>
   ```

## CSS File Location
The typography standards are loaded from: `assets/css/typography-standards.css`

This file is automatically included in all pages through `head.php`.
