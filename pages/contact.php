<!DOCTYPE html>
<html lang="en">

<head>
   
    <?php
        include("./courses_css.php");
    ?>
</head>

<body>
    
     <?php 
             include("../includes/navbar.php");
     ?>
    

    <!-- Contact Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h1 class="mb-5 bg-white text-center px-3">Contact Us</h1>

            </div>
            <div class="row g-4">
                <div class="col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <h5>Get In Touch</h5>
                    <p class="mb-4">I'm happy to help! If you're looking for contact information or details about
                        E-learning Platform online free courses website for e-learning, I don't have real-time browsing
                        capabilities to access current websites or specific contact details.</p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 50px; height: 50px; background-color: #fb873f;">
                            <i class="fa fa-map-marker-alt text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Office</h5>
                            <p class="mb-0">Sanepa, Lalitpur</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 50px; height: 50px; background-color: #fb873f;">
                            <i class="fa fa-phone-alt text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Mobile</h5>
                            <p class="mb-0">+977-1234567890</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 50px; height: 50px; background-color: #fb873f;">
                            <i class="fa fa-envelope-open text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Email</h5>
                            <p class="mb-0">eplatform@gmail.com</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 wow fadeInUp" data-wow-delay="0.5s">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" required placeholder="Your Name">
                                    <label for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" required
                                        placeholder="Your Email">
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" required placeholder="Subject">
                                    <label for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" required placeholder="Leave a message here"
                                        id="message" style="height: 150px"></textarea>
                                    <label for="message">Message</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3" type="submit"><a
                                        href="mailto:keertidvcorai@gmail.com" class="text-decoration-none"
                                        style="color: #fff;" type="submit">Send Message</a></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->


    <?php
            include("../includes/footer.php");
     ?>

</body>

</html>
