<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>P-Ride | Schedule Your Ride, Your Way</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/home.css"><link rel="stylesheet" href="./css/home2.css">
    <link rel="stylesheet" href="./css/how-carousel.css">
  
    <style>
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

</style>
</head>
<body>
    <div class="navbar" sticky>
        <img src="./images/3dblueicon.png" alt="P-Ride Logo" class="navbar-logo">
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="rider_register.php">Become a Rider</a>
            <a href="passenger_register.php">Join as Passenger</a>
            <a href="how.php" active>How</a>
        </div>
        <div class="hamburger" id="hamburger-menu" aria-label="Open menu" tabindex="0">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="mobile-menu" id="mobile-menu">
            <a href="index.php">Home</a>
            <a href="rider_register.php">Become a Rider</a>
            <a href="passenger_register.php">Join as Passenger</a>
            <a href="how.php" active>How</a>
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

    
   




 

 <section class="about-us fade-in-section">
    <div class="how-carousel">
        <!-- Slide 1 -->
        <input type="radio" name="how-slide" id="how-slide-1" checked>
        <input type="radio" name="how-slide" id="how-slide-2">
        <input type="radio" name="how-slide" id="how-slide-3">
        <input type="radio" name="how-slide" id="how-slide-4">
        <input type="radio" name="how-slide" id="how-slide-5">
        <input type="radio" name="how-slide" id="how-slide-6">
        <input type="radio" name="how-slide" id="how-slide-7">
        <input type="radio" name="how-slide" id="how-slide-8">
        <input type="radio" name="how-slide" id="how-slide-9">
        <input type="radio" name="how-slide" id="how-slide-10">
        <input type="radio" name="how-slide" id="how-slide-11">
        <input type="radio" name="how-slide" id="how-slide-12">
        <input type="radio" name="how-slide" id="how-slide-13">
        <input type="radio" name="how-slide" id="how-slide-14">
        <input type="radio" name="how-slide" id="how-slide-15">
        

        <div class="how-slides">
            <!-- Slide 1 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p1.png" alt="Registration & KYC">
                </div>
                <div class="how-text right">
                    <h2>Introducing P-Ride</h2>
                    <ul>
                        <li>the smarter, safer, and more trusted </li>
                        <li>way to move around your city.</li>
                        
                    </ul>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Trip Scheduling built to comfort</h2>
                    <ul>
                        <li>Looking for the easiest way to flow through your route?</li>
                        
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p2.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p3.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2> You could be a Rider/Passenger</h2>
                    <ul>
                        <li>Want to And make your daily trips with ease and enjoy the maximum comfort in a joint private ride?</li>
                        
                    </ul>
                </div>
            </div>

            <!-- Slide 4 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Its Finally Here</h2>
                    <ul>
                        <li>How To Go About It?</li>
                        <li>Scroll-right to continue on how it works!!!</li>
                        
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p4.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 5 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p5.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2>Register</h2>
                    <ul>
                        <li>Join p-ride as a Rider who has a car and wants to go on usual trips.
                             it coulud be the normal day-to-day route to work or occational places</li>

                             <li>Join as a passenger to attached to a rider who is
                                 going your route with no more to the transport fare</li>
                        
                    </ul>
                </div>
            </div>

            <!-- Slide 6 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Trip Scheduling (Rider)</h2>
                    <ul>
                        <li>Login to create a trip, schedule your trip,trip time add the number of seats avaliable, number of passengers needed, prices, origin, intermediate and destination bus-stops for pick-up and drop-off, set the price for the trip and submit.</li>
                       
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p6.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 7 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p7.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2>Trip Booking (Passenger)</h2>
                    <ul>
                        <li>Login to view scheduled trip by the riders,Passengers search trips by location, route, or bus stop</li>
                        <li>They book available seats (or more depending on the avaliable seats)by selecting pick-up and drop-off points.</li>
                        <li>Booking holds the trip fare in a temporary trip account until trip completion</li>
                    </ul>
                </div>
            </div>

            <!-- Slide 8 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Rider Confirmation</h2>
                    <ul>
                        <li>Confirms booking, approves passenger and get connected for meet-up point and in scheduled time. After pick-up and drop-off, rider confirms drop-off of all passengers and mark all trips as completed thus will trigger payments from each passenger.</li>
                        
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p8.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 9 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p9.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2>Passenger Payment</h2>
                    <ul>
                        <li>Recieves payment trigger notifications and release the payment to the rider and the funds is been transfered to the riders account after passengers confirmation.</li>
                      
                    </ul>
                </div>
            </div>

            <!-- Slide 10 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Rating </h2>
                    <ul>
                        <li>Can rate each other based on performance after the trip for transperency, timeliness and management to get more stars ad trip points which will boast honesty and confidence for the User and features.</li>
                    
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p10.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 11 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p11.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2>Trip Execution</h2>
                    <ul>
                        <li>Riders receive booking alerts and pick up passengers as scheduled.</li>
                        <li>Riders can cancel trips 4 hours ahead; passengers get SMS alerts and can rebook at no extra cost.</li>
                        
                    </ul>
                </div>
            </div>

            <!-- Slide 12 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Fact to Note!!!</h2>
                    <ul>
                        <li>This system ensures secure payments, verified users, and efficient local transport all managed in real-time.
</li>
                      
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p12.png" alt="Trip Scheduling">
                </div>
            </div>
            <!-- Slide 13 -->
            <div class="how-slide">
                <div class="how-img left">
                    <img src="./images/p13.png" alt="Trip Booking">
                </div>
                <div class="how-text right">
                    <h2>Join Today</h2>
                    <ul>
                        <li>And take tripping to the Next Level!!!</li>
                        <li>www.p-ride.drive</li>
                        
                    </ul>
                </div>
            </div>

            <!-- Slide 14 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>Be part of the innovative transport system</h2>
                    <ul>
                        <li>What are you waiting for???</li>
                        
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p14.png" alt="Trip Scheduling">
                </div>
            </div>
          
            <!-- Slide 15 -->
            <div class="how-slide">
                <div class="how-text left">
                    <h2>P-Ride</h2>
                    <ul>
                        <li>Your Seamless,Confortable tripping system</li>
                        <li>www.p-ride.drive</li>
                       
                    </ul>
                </div>
                <div class="how-img right">
                    <img src="./images/p15.png" alt="Payment & Completion">
                </div>
            </div>


            



        </div>

        <!-- Carousel Navigation Dots -->
        <div class="how-nav">
            <label for="how-slide-1"></label>
            <label for="how-slide-2"></label>
            <label for="how-slide-3"></label>
              <label for="how-slide-4"></label>
            <label for="how-slide-5"></label>
              <label for="how-slide-6"></label>
            <label for="how-slide-7"></label>
              <label for="how-slide-8"></label>
            <label for="how-slide-9"></label>
              <label for="how-slide-10"></label>
            <label for="how-slide-11"></label>
              <label for="how-slide-12"></label>
            <label for="how-slide-13"></label>
              <label for="how-slide-14"></label>
            <label for="how-slide-15"></label>
        </div>
    </div>
</section>   



<footer class="footer">
    <div class="footer-main">
        <div class="footer-brand">
            <img src="./images/3dwhiteicon.png" alt="P-Ride Logo" class="footer-logo-img">
            <div class="footer-brand-title">P-Ride</div>
            <div class="footer-brand-caption">Your Route, Your Ride, Your Time</div>
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



</body>
</html>