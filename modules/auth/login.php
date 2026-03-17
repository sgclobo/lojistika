<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-6">
                        <h3 class="mb-2">Sign in to LMS</h3>
                        <p class="text-muted mb-4">Use one of the existing role accounts below to access the system.</p>

                        <form method="post" action="index.php?page=login" novalidate>
                            <input type="hidden" name="action" value="login">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required autocomplete="username" placeholder="admin@lms.local">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password" placeholder="Enter password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Sign In</button>
                        </form>
                    </div>

                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <h6 class="text-uppercase text-muted mb-3">Demo Credentials</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent px-0">
                                    <div class="fw-semibold">Admin</div>
                                    <div class="small">Email: <code>admin@lms.local</code></div>
                                    <div class="small">Password: <code>ChangeMe@123</code></div>
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <div class="fw-semibold">Warehouse</div>
                                    <div class="small">Email: <code>warehouse@lms.local</code></div>
                                    <div class="small">Password: <code>ChangeMe@123</code></div>
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <div class="fw-semibold">Requester</div>
                                    <div class="small">Email: <code>requester@lms.local</code></div>
                                    <div class="small">Password: <code>ChangeMe@123</code></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>