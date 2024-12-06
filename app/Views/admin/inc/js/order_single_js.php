<script>
    lode_order();

    $('#order_status_select_bx').on('change', function () {

        // Get the selected value of the select box
        var order_status = $(this).val();
        $.ajax({
            url: "<?= base_url('/api/order/status/update') ?>",
            data: {
                order_status: order_status,
                o_id: "<?= $_GET['o_id'] ?>"
            },
            beforeSend: function () {

            },
            success: function (resp) {
                lode_order()
            },
            error: function (err) {
                console.error(err)
            }
        })


    })


    function lode_order() {
        $.ajax({
            url: '<?= base_url('/api/order') ?>',
            type: 'GET',
            data: {
                o_id: '<?= $_GET['o_id'] ?>'
            },
            beforeSend: function () { },
            success: function (resp) {
                console.log(resp)
                if (resp.status) {
                    let order = resp.data
                    $('#order_status_select_bx').val(order.order_status)
                    $('#user_bx').html(
                        `<li>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img 
                                                style="height: 50px; width: 50px; object-fit: contain; background: white;"  
                                                src="https://www.kindpng.com/picc/m/24-248253_user-profile-default-image-png-clipart-png-download.png" 
                                                alt="" 
                                                class="avatar-sm rounded material-shadow">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="fs-14 mb-1">${order.user.user_name}</h6>
                                            <p class="text-muted mb-0">Customer</p>
                                        </div>
                                    </div>
                                </li>
                                <li><i class="ri-mail-line me-2 align-middle text-muted fs-16"></i>${order.user.email}</li>
                                <li><i class="ri-phone-line me-2 align-middle text-muted fs-16"></i>${order.user.number}</li>`)

                    $('#user_addr_bx').html(`<ul class="list-unstyled vstack gap-2 fs-13 mb-0">
                                                <li class="fw-medium fs-14">${order.user_name}</li>
                                                <li>Phone - ${order.phone_number}</li>
                                                <li>Landmark - ${order.address.locality}</li>
                                                <li>City - ${order.address.city}, Street Address - ${order.address.district}</li>
                                                <li>State - ${order.address.state} , Country - ${order.address.country}</li>
                                                <li>Postal Code - ${order.address.zipcode}</li>
                                            </ul>`)
                    $('#user_pay_bx').html(`
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Update:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <select class="form-select form-select-md" id="${order.payment.uid}" onChange="update_order_payment('${order.payment.uid}')">
                                    <option value="pending" ${order.payment.status == 'pending' ? 'selected' : ''}>pending</option>
                                    <option value="paid" ${order.payment.status == 'paid' ? 'selected' : ''}>paid</option>
                                </select>
                            </div>
                            
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Pay Id:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0">${order.payment.uid}</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Payment Method:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0">${order.payment.type}</h6>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Payment Status:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0">${order.payment.status}</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Total Amount:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"> ${order.total}</h6>
                            </div>
                        </div>`)


                    $('#order_id').html(order.order_id)
                    let html = ``
                    let total_discount = 0
                    let delivery_charge = 0
                    $.each(order.products, function (index, item) {
                        let actual_price = ''
                        $.each(item.product_details.product_prices, function(index, prices) {
                            // console.log(prices)
                            if(parseInt(item.qty)>= parseInt(prices.min_qty) && parseInt(item.qty) <= parseInt(prices.max_qty)){
                                actual_price = prices.price
                                // subTotal += parseInt(actual_price) * parseInt(item.qty)
                            }
                            
                        })
                        // let actual_price = item.product_details.base_price; // Base price of the product
                        let qty = item.qty; // Quantity of the product
                        let base_discount = parseInt(item.product_details.base_discount); // Discount percentage
                        let tax = parseInt(item.product_details.tax); // Tax percentage
                        let discounted_price = actual_price * qty - ((actual_price * qty) * base_discount / 100);
                        let tax_amount = discounted_price * tax / 100;
                        let final_price = discounted_price + tax_amount;
                        delivery_charge += parseInt(item.product_details.delivery_charge)
                         let img_link = item.product_config_id ? '/public/uploads/variant_images/' : '/public/uploads/product_images/'
                        html += `    <tr>
                                        <td>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 avatar-md bg-light rounded p-1">
                                                    <img
                                                        style="height: 100%; width: 100%; object-fit: contain; background: white;"
                                                        src="<?= base_url() ?>${img_link + item.product_details.product_img[0].src}" 
                                                        alt="" 
                                                        class="img-fluid d-block">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="fs-15">
                                                        <a href="" class="link-primary">${item.product_details.name.substring(0, 25) + "..."}</a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₹ ${actual_price}</td>
                                        <td>${item.qty}</td>
                                        <td>${item.size}</td>
                                        <td>${item.product_details.base_discount}%</td>
                                        <td>${item.product_details.tax}%</td>
                                        
                                        <td class="fw-medium text-end">
                                            <!-- ${(actual_price * item.qty).toFixed(2)} -->
                                            ₹${final_price.toFixed(2)}
                                        </td>
                                    </tr>`

                                    total_discount += parseInt(item.product_details.base_discount, 10);
                    })
                    html += `<tr class="border-top border-top-dashed">
                                <td colspan="3"></td>
                                <td colspan="2" class="fw-medium p-0">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td>Sub Total :</td>
                                                <td class="text-end"> ₹ ${order.sub_total}</td>
                                            </tr>
                                            <!-- <tr>
                                                <td>Discount : </td>
                                                <td class="text-end">${total_discount}%</td>
                                            </tr> -->
                                            <tr>
                                                <td>Shipping Charge :</td>
                                                <td class="text-end">₹ ${delivery_charge}</td>
                                            </tr>
                                            <tr class="border-top border-top-dashed">
                                                <th scope="row">Total (INR) :</th>
                                                <th class="text-end">₹ ${order.total}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>`
                    $('#product_table_body').html(html)

                    // $('#').html(` <div class="accordion accordion-flush" id="accordionFlushExample">
                    //             <div class="accordion-item border-0">
                    //                 <div class="accordion-header" id="headingOne">
                    //                     <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse"
                    //                         href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    //                         <div class="d-flex align-items-center">
                    //                             <div class="flex-shrink-0 avatar-xs">
                    //                                 <div class="avatar-title bg-success rounded-circle material-shadow">
                    //                                     <i class="ri-shopping-bag-line"></i>
                    //                                 </div>
                    //                             </div>
                    //                             <div class="flex-grow-1 ms-3">
                    //                                 <h6 class="fs-15 mb-0 fw-semibold">Order Placed </h6>
                    //                             </div>
                    //                         </div>
                    //                     </a>
                    //                 </div>
                    //             </div>
                    //             <div class="accordion-item border-0">
                    //                 <div class="accordion-header" id="headingThree">
                    //                     <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse"
                    //                         href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    //                         <div class="d-flex align-items-center">
                    //                             <div class="flex-shrink-0 avatar-xs">
                    //                                 <div class="avatar-title bg-success rounded-circle material-shadow">
                    //                                     <i class="ri-truck-line"></i>
                    //                                 </div>
                    //                             </div>
                    //                             <div class="flex-grow-1 ms-3">
                    //                                 <h6 class="fs-15 mb-1 fw-semibold">Shipping </h6>
                    //                             </div>
                    //                         </div>
                    //                     </a>
                    //                 </div>
                    //             </div>
                    //             <div class="accordion-item border-0">
                    //                 <div class="accordion-header" id="headingFour">
                    //                     <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse"
                    //                         href="#collapseFour" aria-expanded="false">
                    //                         <div class="d-flex align-items-center">
                    //                             <div class="flex-shrink-0 avatar-xs">
                    //                                 <div
                    //                                     class="avatar-title bg-light text-success rounded-circle material-shadow">
                    //                                     <i class="ri-takeaway-fill"></i>
                    //                                 </div>
                    //                             </div>
                    //                             <div class="flex-grow-1 ms-3">
                    //                                 <h6 class="fs-14 mb-0 fw-semibold">Out For Delivery</h6>
                    //                             </div>
                    //                         </div>
                    //                     </a>
                    //                 </div>
                    //             </div>
                    //             <div class="accordion-item border-0">
                    //                 <div class="accordion-header" id="headingFive">
                    //                     <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse"
                    //                         href="#collapseFile" aria-expanded="false">
                    //                         <div class="d-flex align-items-center">
                    //                             <div class="flex-shrink-0 avatar-xs">
                    //                                 <div
                    //                                     class="avatar-title bg-light text-success rounded-circle material-shadow">
                    //                                     <i class="mdi mdi-package-variant"></i>
                    //                                 </div>
                    //                             </div>
                    //                             <div class="flex-grow-1 ms-3">
                    //                                 <h6 class="fs-14 mb-0 fw-semibold">Delivered</h6>
                    //                             </div>
                    //                         </div>
                    //                     </a>
                    //                 </div>
                    //             </div>
                    //         </div>`)


                }

            },
            error: function (err) {
                console.error(err)
            }

        })

    }


    function update_order_payment(pay_id){

        $.ajax({
            url: "<?=base_url('api/order/payment/status/update')?>",
            type: "GET",
            data:{
                pay_id: pay_id,
                status: $(`#${pay_id}`).val()
            },
            beforeSend: function(){},
            success: function(resp){console.log(resp)},
            error: function(err){console.error(err)}
        })
    }









</script>