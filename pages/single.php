<?php
    include("./courses_css.php")
?>
<style>
    .tabs ul li {
        list-style-type: none;
    }

    .tabs ul li a {
        font-size: 25px;
        color: #4e4e4e !important;
        font-weight: 500;
    }

    .tabs ul li a.active {
        color: #f69050 !important;
    }

    .tabs ul li a:hover {
        color: #f69050 !important;
    }

    #more {
        display: none;
    }

    button {
        border: none;
        color: #f69050;
    }
</style>

<body>
   <?php
    include("../includes/navbar.php")
?>



<!-- Course Detail Start -->
<div class="container-xxl py-2">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 wow fadeInUp">

                <div class="container">
                    <div class="row g-5 justify-content-center">

                        <div class="col-lg-12 wow fadeInUp" data-wow-delay="0.3s">
                            <h2>Computer Network For Dummies</h2>
                           
                        </div>
                    </div>
                </div>



                <div class="container-fluid wow fadeInUp mt-5 tabs">


                    <!-- Tab panes -->
                    <div class="tab-content mt-4">

                        <div class="tab-pane container active" id="Overview">
                            <h2>About this Course</h2>
                            <!-- <p>Fun fact: all websites use HTML — even this one. It's a fundamental part of every web developer's toolkit. HTML provides the content that gives web pages structure, by using elements and tags, you can add text, images, videos, forms, and more. Learning HTML basics is an important first step in your web development journey and an essential skill for front- and back-end developers.</p> -->

                        </div>

                        <div class="container" id="Curriculum">

                            <!-- <h2 class="mt-4">
                                Syllabus
                            </h2> -->
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                <div class="accordion-item">
                                  <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                        Elements and Structure
                                    
                                    </button>
                                  </h2>
                                  <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body"><ul>
                                        <a href="note.pdf" target="_blank">Chapter 1</a>

                                        <!-- <li><i class="fa fa-book text-danger"></i> </li>
                                        <li><i class="fa fa-book text-danger"></i> A2</li> -->
                                    </ul></div>
                                  </div>
                                </div>
                              </div>



                        </div>

                        
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>
<!-- Course Detail End -->


<?php
    include("../includes/footer.php")
?>
</body>


</html>
