chrome.runtime.sendMessage({}, function (response) {
    if (response !== undefined && response.action === CTRL_START) {
        setTimeout(function () {
            redirect();
        }, 4000);

        function redirect() {
            // Detect Profile Not Found
            var not_profile_h1 = document.querySelectorAll("#content h1");
            var profile_unavailable = document.querySelectorAll(".profile-unavailable");
            var not_found_404 = document.querySelectorAll(".not-found-404");

            if ((not_profile_h1.length > 0  && not_profile_h1[0].textContent.length > 0 && not_profile_h1[0].textContent.search('Profile Not Found') >= 0) || profile_unavailable.length > 0 || not_found_404.length > 0) {
                chrome.runtime.sendMessage({status: STATUS_DONE, type: STATUS_UNAVAILABLE}, function (response) {});
            } else {
                // View un sales navigator button
                var btn_sales_navigator = document.querySelectorAll("a.pv-s-profile-actions--view-profile-in-sales-navigator");

                // Signin button
                var btn_signin = document.querySelectorAll(".nav-signin");

                if (btn_signin.length > 0) {
                    window.location = LOGIN_LINKEDIN_URL;
                } else {
                    if (btn_sales_navigator.length > 0) {
                        //document.querySelectorAll(".contact-see-more-less")[0].click();
                        setTimeout(function () {
                            // extract url and brithday
                            var birthdayEl = document.querySelectorAll("section .ci-birthday .pv-contact-info__contact-item");
                            var birthdayValue = '';
                            if (birthdayEl.length > 0) {
                                birthdayValue = birthdayEl[0].textContent;
                            }

                            var publicURLEl = document.querySelectorAll("section .pv-contact-info__contact-link");
                            var publicURLValue = window.location.href;
                            if (publicURLEl.length > 0) {
                                publicURLValue = publicURLEl[0].textContent.trim();
                            }

                            var result = {
                                url: publicURLValue,
                                birthday: birthdayValue
                            };

                            chrome.runtime.sendMessage({status: CTRL_REDIRECT, url: btn_sales_navigator[0].getAttribute('href'), result: result }, function (response) {
                                btn_sales_navigator[0].addEventListener("click", function( event ) {
                                    event.preventDefault();
                                }, false);

                                btn_sales_navigator[0].click();
                            });
                        }, 1000);
                    } else {
                        // Button edit
                        var btn_edit = document.querySelectorAll(".pv-top-card-section__edit");
                        if (btn_edit.length > 0) {
                            chrome.runtime.sendMessage({status: STATUS_DONE}, function (response) {});
                        } else {
                            chrome.runtime.sendMessage({status: STATUS_DONE, type: STATUS_UNAVAILABLE}, function (response) {});
                        }
                    }
                }
            }
        }
    }
});
