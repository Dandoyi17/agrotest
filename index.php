<?php
require_once 'db.php';

// Fetch latest 5 vehicles and trips from riders and trips tables
$stmt = $pdo->prepare("
    SELECT r.id AS rider_id, r.name AS rider_name, r.vehicle_image, r.vehicle_info, 
           t.id AS trip_id, t.origin, t.destination,
           (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
           (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
    FROM riders r
    JOIN trips t ON r.id = t.rider_id
    WHERE r.vehicle_image IS NOT NULL AND r.vehicle_image != ''
    ORDER BY t.id DESC
    LIMIT 100
");
$stmt->execute();
$vehicles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>P-Ride | Schedule Your Ride, Your Way</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/home.css"><link rel="stylesheet" href="./css/home2.css">
    <link rel="stylesheet" href="./css/how-carousel.css">
    <!--<style>
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 10px 5vw 10px 5vw;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
            position: relative;
            z-index: 10;
        }
        .navbar-logo {
            height: 48px;
            width: auto;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .navbar-links a {
            color: #040530;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.08em;
            transition: color 0.2s;
        }
        .navbar-links a:hover {
            color: #040546;
        }
        /* Hamburger styles */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            width: 32px;
            height: 32px;
            cursor: pointer;
            margin-left: 20px;
        }
        .hamburger span {
            height: 4px;
            width: 100%;
            background: #040530;
            margin: 4px 0;
            border-radius: 2px;
            transition: 0.3s;
        }
        /* Mobile menu */
        .mobile-menu {
            display: none;
            position: absolute;
            top: 60px;
            right: 5vw;
            background: #fff;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
            border-radius: 10px;
            min-width: 170px;
            z-index: 100;
            flex-direction: column;
            padding: 14px 0;
        }
        .mobile-menu a {
            color: #040530;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 24px;
            font-size: 1.08em;
            border-bottom: 1px solid #e3f2fd;
            transition: background 0.2s;
        }
        .mobile-menu a:last-child {
            border-bottom: none;
        }
        .mobile-menu a:hover {
            background: #e3f2fd;
        }
        /* Hamburger active state */
        .hamburger.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        .mobile-menu.show {
            display: flex;
        }
        @media (max-width: 900px) {
            .navbar-links {
                display: none;
            }
            .hamburger {
                display: flex;
            }
        }
        @media (max-width: 600px) {
            .navbar {
                padding: 10px 4vw;
            }
            .mobile-menu {
                right: 4vw;
            }
        }
    </style>
-->
    <style>

        
 .containa {
            width: 90%;
            height: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 3px 14px #040530;
        }
   .container {
    max-width: 1200px;
    height: auto;
    box-shadow: #040530;
    border-radius: 10px;
    background-color: #040530;
    display: flex;
    position: relative;
    align-content: center;
    margin-left: 8%;
    margin-right: 8%;
    
}

.container .flex{
    margin: 10px;
}

.container .h2 {
    color: #fff;
    letter-spacing: 3px;
    background-color: #040530;
    display: flex;
    border-radius: 10px;
    
}

.container .h3{
    width: 150px;
    height: 50px;
    background-color: #fff;
    color: #040530;
    display: inline;
    text-align: center;
    align-content: center;
    border-radius: 50px;
    margin-left: 70%;

}
.container a{
    text-decoration: none;
    
}

.container a:hover{
    color: #fff;
}
.container .h3:hover{
    background-color: #040546;
    color: #fff;
    border: 2px;
    border-color: #fff;
}

.container .appvideo {
    display: flex;
    width: 100%;
    height: auto;
    border-radius: 10px;
}
 @media (max-width: 900px) {
            .container {
                display: flex;
                align-items: center;
            }
            
        }
 @media (max-width: 600px) {
            .container {
                display: flex;
                align-items: center;
            }
            
        }

/*
        .more{
            width: 80%;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #040530;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 5px;

        }
*/
        .more {
    width: 80%;
    max-width: 700px;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #040530;
    margin-top: 20px;
    margin-left: auto;
    margin-right: auto;
    border-radius: 5px;
    position: relative;
    overflow: hidden;
}
.more-slider {
    width: 100%;
    height: 100%;
    position: relative;
}
.more-slide {
    display: none;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
    position: absolute;
    left: 0; top: 0;
    transition: opacity 0.5s;
    background: #040530;
    padding: 24px;
    box-sizing: border-box;
}
.more-slide.active {
    display: flex;
    opacity: 1;
    z-index: 2;
}
.more-logo {
    margin-right: 32px;
    box-shadow: 0 2px 12px rgba(25, 118, 210, 0.09);
    border: 2px solid #1976d2;
}
.more-info {
    color: #fff;
    max-width: 320px;
}
.more-info h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #fff;
    font-size: 1.3em;
}
.more-info p {
    margin: 4px 0;
    font-size: 1em;
}
.more-btn {
    display: inline-block;
    margin-top: 12px;
    background: #1976d2;
    color: #fff;
    padding: 8px 22px;
    border-radius: 5px;
    font-size: 1em;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}
.more-btn:hover {
    background: #1565c0;
}
.more-nav {
    position: absolute;
    bottom: 18px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}
.more-nav .dot {
    width: 14px;
    height: 14px;
    background: #3949ab;
    border-radius: 50%;
    cursor: pointer;
    opacity: 0.6;
    transition: background 0.2s;
    border: 2px solid #fff;
}
.more-nav .dot.active {
    background: #1976d2;
    opacity: 1;
    border-color: #1976d2;
}
@media (max-width: 700px) {
    .more {
        width: 98vw;
        max-width: 98vw;
        height: 340px;
        padding: 0;
    }
    .more-slide {
        flex-direction: column;
        padding: 10px;
    }
    .more-logo {
        margin-right: 0;
        margin-bottom: 18px;
    }
}

</style>
</head>
<body onmousemove="let span = document.createElement ('span'); span.innerText='🚗'; span.style=`position:absolute; left:${event.clientX}px; top:${event.clientY}px`; document.body.append(span); setTimeout(()=>span.remove(), 500)">
    <div class="navbar">
        <img src="./images/3dblueicon.png" alt="P-Ride Logo" class="navbar-logo">
        <div class="navbar-links">
            
            <a href="login.php" active>Login</a>
            <a href="rider_register.php">Become a Rider</a>
            <a href="passenger_register.php">Join as Passenger</a>
            <a href="how.php">How?</a>
        </div>
        <div class="hamburger" id="hamburger-menu" aria-label="Open menu" tabindex="0">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="mobile-menu" id="mobile-menu">
            <a href="login.php" active>Login</a>
            <a href="rider_register.php">Become a Rider</a>
            <a href="passenger_register.php">Join as Passenger</a>
            <a href="how.php">How?</a>
        </div>
    </div>
    <script>
        const hamburger = document.getElementById('hamburger-menu');
        const mobileMenu = document.getElementById('mobile-menu');
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('show');
            }
        });
        hamburger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                hamburger.classList.toggle('active');
                mobileMenu.classList.toggle('show');
            }
        });
    </script>

    <section class="hero">
    <video autoplay loop muted playsinline class="hero-bg-video">
        <source src="./videos/test.mp4" type="video/mp4">
        
    </video>
    <img src="./images/3dbluelogo.png" alt="P-Ride Logo" class="hero-logo">
    <div class="hero-title">Your Route, Your Ride, Your Time</div>
    <div class="hero-caption">
        Discover a smarter way to Transport with Comfort.<br>
        <b>Book available trips instantly</b> scheduled by riders heading your way.<br>
        <span style="color:#fff;">Seamless, Safe, and Affordable ride scheduling for Everyone.</span>
    </div>
    <div class="hero-cta">
        <a href="search_trips.php" class="hero-btn">Book a Ride</a>
        <a href="rider_register.php" class="hero-btn alt">Become a Rider</a>
    </div>
</section>
<div class="containa">
   <section class="about" >
    <div class="experience">
        <div class="why">Why Choose P-Ride?</div>
        Experience the future of transportation with P-Ride's innovative ride scheduling platform.<br>
        Connect with verified riders and passengers, ensuring safe and reliable journeys every time.
    </div>
    <video src="./images/vidblue.mp4" autoplay loop muted class="clip"></video>
</section>





    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">🚗</div>
            <div class="feature-title">Instant Trip Booking</div>
            <div class="feature-desc">Find and book rides scheduled by drivers going your way—no waiting, no hassle.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <div class="feature-title">Safe & Secure</div>
            <div class="feature-desc">Verified riders and passengers, secure payments, and real-time support for your peace of mind.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💸</div>
            <div class="feature-title">Affordable for All</div>
            <div class="feature-desc">Enjoy competitive fares and flexible options for every journey—wherever you’re headed.</div>
        </div>
    </section>

 <section class="about-us fade-in-section">
    <div class="how-carousel">
        <!-- Slide 1 -->
        <input type="radio" name="how-slide" id="how-slide-1" checked>
        <input type="radio" name="how-slide" id="how-slide-2">
        <input type="radio" name="how-slide" id="how-slide-3">
        <input type="radio" name="how-slide" id="how-slide-4">
        <input type="radio" name="how-slide" id="how-slide-4">

        <div class="how-slides">
            <!-- Slide 1 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/register.png" alt="Registration & KYC">
                </div>
                <div class="how-text right">
                    <h2>Registration & KYC</h2>
                    <ul>
                        <li>Register as Rider or Passenger</li>
                        <li>Complete KYC (ID, address, phone, etc.)</li>
                        <li>Ensures accountability for all users</li>
                    </ul>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Trip Scheduling (Rider)</h2>
                    <ul>
                        <li>Riders upload vehicle details & create trip schedules</li>
                        <li>Select pick-up/drop-off, seats, time, price</li>
                        <li>Pick up passengers along the route</li>
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/login.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/trip.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2>Trip Booking (Passenger)</h2>
                    <ul>
                        <li>Search trips by location, route, or bus stop</li>
                        <li>View available trips, rider & vehicle info</li>
                        <li>Book seats for yourself or friends</li>
                    </ul>
                </div>
            </div>
            <!-- Slide 4 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Payment & Completion</h2>
                    <ul>
                        <li>Pay securely online, fare held until trip ends</li>
                        <li>Riders confirm drop-off, payment released</li>
                        <li>Rate your experience, riders get paid</li>
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/3dbluelogo.png" alt="Payment & Completion">
                </div>
            </div>
        </div>

        <!-- Carousel Navigation Dots -->
        <div class="how-nav">
            <label for="how-slide-1"></label>
            <label for="how-slide-2"></label>
            <label for="how-slide-3"></label>
            <label for="how-slide-4"></label>
        </div>
    </div>
</section>   

<section class="container">
    <div class="flex">
            <div class="h2"><h2>Get our App</h2>
            <h3 class="h3">Download</a></h3></div>
            <div>
                <video autoplay loop muted playsinline class="appvideo">
                <source src="./videos/app.mp4" type="video/mp4">
                </video> 
        </div>
    </div>
</section>
<h1 class="btn">Popular Route</h1>
<section class="more">
    <div class="more-slider">
        <?php foreach ($vehicles as $i => $v): ?>
        <div class="more-slide<?php echo $i === 0 ? ' active' : ''; ?>">
            <img src="<?php echo htmlspecialchars($v['vehicle_image']); ?>" alt="Vehicle Image" class="more-logo" style="width:220px;height:140px;object-fit:cover;border-radius:10px;">
            <div class="more-info">
                <h3><?php echo htmlspecialchars($v['vehicle_info']); ?></h3>
                <p><strong>Rider:</strong> <?php echo htmlspecialchars($v['rider_name']); ?></p>
                <p><strong>Origin:</strong> <?php echo htmlspecialchars($v['origin_name']); ?></p>
                <p><strong>Destination:</strong> <?php echo htmlspecialchars($v['destination_name']); ?></p>
                <a href="login.php?id=<?php echo $v['trip_id']; ?>" class="more-btn">View Details</a>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="more-nav">
            <?php foreach ($vehicles as $i => $v): ?>
                <span class="dot<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
    


<footer class="footer">
    <div class="footer-main">
        <div class="footer-brand">
            <img src="./images/3dwhiteicon.png" alt="P-Ride Logo" class="footer-logo-img">
            <div class="footer-brand-title">P-Ride</div>
            <div class="footer-brand-caption">Your Route, Your Ride, Your Time<br>P-Ride is a private car ride<br>
             website that will be service<br> to connecting private car owners to<br> 
             passengers who are going thesame route.<br> click on the <a href="how.php">How</a> to see full details</div>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <div class="footer-col-title">Company</div>
                <a href="#">About Us</a>
                <a href="#">How it Works</a>
                <a href="#">Careers</a>
                <a href="#">Blog</a>
            </div>
            <div class="footer-col">
                <div class="footer-col-title">Support</div>
                <a href="mailto:support@p-ride.com">support@p-ride.com</a>
                 <a href="mailto:support@p-ride.com">info@p-ride.com</a>
                <a href="tel:+2348000000000">+234 704 744 0709</a>
                <a href="#">Help Center</a>
                <a href="#">Safety</a>
            </div>
            <div class="footer-col">
                <div class="footer-col-title">Contact</div>
                <div>reach us on our contact email and phone Numbers</div>
                <div>Open all Mon - Sun, 8:00am - 6:00pm</div>
                <div class="subscribe" ><button>Subscribe</button><i><input type="text" placeholder="Newsletter"></i></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date('Y'); ?> P-Ride. All rights reserved.
    </div>
</footer>

</div>


<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.more-slide');
const dots = document.querySelectorAll('.more-nav .dot');
function showSlide(idx) {
    slides.forEach((s, i) => s.classList.toggle('active', i === idx));
    dots.forEach((d, i) => d.classList.toggle('active', i === idx));
    currentSlide = idx;
}
function nextSlide() {
    let idx = (currentSlide + 1) % slides.length;
    showSlide(idx);
}
dots.forEach((dot, i) => {
    dot.addEventListener('click', () => showSlide(i));
});
setInterval(nextSlide, 4000); // Auto-slide every 4 seconds
</script>
</body>
</html>