<section class="login-shell">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            <div class="card login-card border-0">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-6 login-panel">
                            <div class="p-4 p-lg-5">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="assets/img/aifaesa.png" alt="AIFAESA Logo" class="login-logo me-2">
                                    <div>
                                        <div class="login-eyebrow">AIFAESA Inventory</div>
                                        <h2 class="h4 mb-0">Sign in to Lojistika</h2>
                                    </div>
                                </div>
                                <p class="text-muted mb-4">Enter your account credentials to continue.</p>

                                <form method="post" action="index.php?page=login" novalidate>
                                    <input type="hidden" name="action" value="login">

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email"
                                            required autocomplete="username" placeholder="admin@lms.local">
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control form-control-lg" id="password"
                                            name="password" required autocomplete="current-password"
                                            placeholder="Enter password">
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100">Sign In</button>
                                    <a href="index.php?page=register"
                                        class="btn btn-outline-secondary btn-lg w-100 mt-2">Create New User</a>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-6 login-aside">
                            <div class="p-4 p-lg-5 h-100 d-flex flex-column justify-content-center">
                                <h6 class="text-uppercase mb-3">Demo Credentials</h6>
                                <div class="small mb-3">Use these users to test each role in demo mode.</div>

                                <!-- <ul class="list-group list-group-flush login-creds-list">
                                    <li class="list-group-item bg-transparent px-0">
                                        <div class="fw-semibold">Admin</div>
                                        <div>Email: <code>admin@lms.local</code></div>
                                        <div>Password: <code>ChangeMe@123</code></div>
                                    </li>
                                    <li class="list-group-item bg-transparent px-0">
                                        <div class="fw-semibold">Warehouse</div>
                                        <div>Email: <code>warehouse@lms.local</code></div>
                                        <div>Password: <code>ChangeMe@123</code></div>
                                    </li>
                                    <li class="list-group-item bg-transparent px-0">
                                        <div class="fw-semibold">Requester</div>
                                        <div>Email: <code>requester@lms.local</code></div>
                                        <div>Password: <code>ChangeMe@123</code></div>
                                    </li>
                                </ul> -->
                                <img src="assets/img/aifaesa.png" alt="Login Illustration"
                                    class="login-illustration mt-4">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>