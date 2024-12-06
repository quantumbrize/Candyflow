<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="h-100">
                    <div class="row mb-3 pb-1">
                        <div class="col-12">
                            <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                <div class="mt-3 mt-lg-0">
                                    <form action="javascript:void(0);">
                                        <div class="row g-3 mb-0 align-items-center">


                                            <!-- Button to trigger modal -->
                                            <div class="col-auto">
                                                <a href="#" type="button"
                                                    class="btn btn-soft-success material-shadow-none"
                                                    data-bs-toggle="modal" data-bs-target="#authorizationLetterModal">
                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                    Authorization Letter
                                                </a>
                                            </div>

                                            <!-- Modal Structure -->

                                            <div class="modal fade" id="authorizationLetterModal" tabindex="-1"
                                                aria-labelledby="authorizationLetterModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="authorizationLetterModalLabel">
                                                                Authorization Letter</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Form to input image and description -->
                                                            <form id="authorizationForm">
                                                                <div class="mb-3">
                                                                    <label for="authImage" class="form-label">Upload
                                                                        Image</label>
                                                                    <input type="file" class="form-control"
                                                                        id="authImage" accept="image/*" multiple>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="authDescription"
                                                                        class="form-label">Description</label>
                                                                    <textarea class="form-control" id="authDescription"
                                                                        rows="3"
                                                                        placeholder="Enter your description here"></textarea>
                                                                </div>

                                                                <button type="submit" id="submit_authorization"
                                                                    class="btn btn-primary">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>




                                        </div>
                                        <!--end row-->
                                    </form>
                                </div>
                            </div>
                            <!-- end card header -->
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->

                    <div class="row">
                        <!-- <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Total Products
                                            </p>
                                        </div>
                                       
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value" id="product_counter"
                                                    data-target="559.25"></span>
                                            </h4>
                                            <a href="<?= base_url('admin/product/list') ?>"
                                                class="text-decoration-underline">View all products</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                                <i class="bx bx-dollar-circle text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>  -->

                        <div class="col-xl-4 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Total Items Orderd
                                            </p>
                                        </div>
                                        <!-- <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                                                -3.57 %
                                            </h5>
                                        </div> -->
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value" id="order_counter"
                                                    data-target="36894">0</span>
                                            </h4>
                                            <a href="<?= base_url('admin/orders') ?>"
                                                class="text-decoration-underline">View all orders</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                                <i class="bx bx-shopping-bag text-info"></i>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->


                        <div class="col-xl-4 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Erning
                                            </p>
                                        </div>
                                        <!-- <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>
                                        </div> -->
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                ₹<span class="counter-value" id="erning_counter"
                                                    data-target="0">0</span>
                                            </h4>
                                            <a href="" class="text-decoration-underline">See details</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-wallet text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Ready to Ship

                                            </p>
                                        </div>
                                        <!-- <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>
                                        </div> -->
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                ₹<span class="counter-value" id=""
                                                    data-target="0">0</span>
                                            </h4>
                                            <a href="" class="text-decoration-underline">See details</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-wallet text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>




                        <div class="col-xl-4 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Shipped

                                            </p>
                                        </div>
                                        <!-- <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>
                                        </div> -->
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                ₹<span class="counter-value" id=""
                                                    data-target="0">0</span>
                                            </h4>
                                            <a href="" class="text-decoration-underline">See details</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-wallet text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>


                        
                        <div class="col-xl-4 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Delivered 

                                            </p>
                                        </div>
                                        <!-- <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>
                                        </div> -->
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                ₹<span class="counter-value" id=""
                                                    data-target="0">0</span>
                                            </h4>
                                            <a href="" class="text-decoration-underline">See details</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-wallet text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>


                          
                        <div class="col-xl-4 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Canceled 

                                            </p>
                                        </div>
                                        <!-- <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>
                                        </div> -->
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                ₹<span class="counter-value" id=""
                                                    data-target="100">0</span>
                                            </h4>
                                            <a href="" class="text-decoration-underline">See details</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-wallet text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>

                        
                        <!-- end col -->
                    </div>
                    <!-- end row-->

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Revenue</h4>
                                    <div>
                                        <button type="button"
                                            class="btn btn-soft-secondary material-shadow-none btn-sm">
                                            ALL
                                        </button>
                                        <button type="button"
                                            class="btn btn-soft-secondary material-shadow-none btn-sm">
                                            1M
                                        </button>
                                        <button type="button"
                                            class="btn btn-soft-secondary material-shadow-none btn-sm">
                                            6M
                                        </button>
                                        <button type="button" class="btn btn-soft-primary material-shadow-none btn-sm">
                                            1Y
                                        </button>
                                    </div>
                                </div>
                                <!-- end card header -->
                                <div class="card-header p-0 border-0 bg-light-subtle">
                                    <div class="row g-0 text-center">
                                        <div class="col-6 col-sm-6">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1">
                                                    <span class="counter-value" id="delivered_orders"
                                                        data-target="">0</span>
                                                </h5>
                                                <p class="text-with-line text-muted mb-0">Deliverd Orders</p>
                                            </div>
                                        </div>
                                        <!--end col-->
                                        <!-- <div class="col-6 col-sm-4">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1">
                                                    <span class="counter-value" id="total_erning" data-target="">0</span>
                                                </h5>
                                                <p class="text-with-line2 text-muted mb-0">Earnings</p>
                                            </div>
                                        </div> -->
                                        <!--end col-->
                                        <div class="col-6 col-sm-6">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1">
                                                    <span class="counter-value" id="cancelled_orders"
                                                        data-target="">0</span>
                                                </h5>
                                                <p class="text-with-line3 text-muted mb-0">Refunds</p>
                                            </div>
                                        </div>
                                        <!--end col-->
                                        <!-- <div class="col-6 col-sm-3">
                                            <div class="p-3 border border-dashed border-start-0 border-end-0">
                                                <h5 class="mb-1 text-success">
                                                    <span class="counter-value" data-target="18.92">0</span>%
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    Conversation Ratio
                                                </p>
                                            </div>
                                        </div> -->
                                        <!--end col-->
                                    </div>
                                </div>
                                <!-- end card header -->

                                <div class="card-body p-0 pb-2">
                                    <canvas id="myChart" style="width:100%;max-width:100%"></canvas>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->


                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        Best Selling
                                    </h4>

                                </div>
                                <!-- end card header -->

                                <div class="card-body" style="overflow-x: scroll;">

                                    <table id="table-best-selling-list-all" class="table nowrap table-hover"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Product</th>
                                                <th style="text-align: center;">Name</th>
                                                <th style="text-align: center;">Price</th>
                                                <th style="text-align: center;">Order</th>
                                                <th style="text-align: center;">Stock</th>
                                                <th style="text-align: center;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-best-selling-list-all-body">

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                        <!-- .col-->
                    </div>

                    <!-- end row-->
                </div>
                <!-- end .h-100-->
            </div>
            <!-- end col -->


        </div>
    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->