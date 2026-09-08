<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>College Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="assets/css/style.css?v=4">
</head>

<body>

<div class="login-wrapper">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">

            <div class="col-lg-10">

                <div class="login-card">

                    <div class="row g-0">

                        <!-- LEFT SIDE -->
                        <div class="col-lg-6 login-banner">

                            <div class="banner-content">

                                <div class="brand-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>

                                <h1>Silver Oak Institute</h1>

                                <p>
                                    Manage students, faculty, attendance,
                                    results, fees and academic activities
                                    from one smart platform.
                                </p>

                                <div class="feature-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Student Management
                                </div>

                                <div class="feature-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Attendance & Results
                                </div>

                                <div class="feature-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Faculty Management
                                </div>

                            </div>

                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-lg-6 login-form-section">

                            <div class="login-form-box">

                                <div class="mb-4">

                                    <span class="small-title">
                                        WELCOME BACK
                                    </span>

                                    <h2>Sign in to your account</h2>

                                    <p class="text-muted">
                                        Enter your credentials to continue
                                    </p>

                                </div>
                                
                                <!-- Form  -->
                                <form action="login.php" method="POST">

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Email Address
                                        </label>

                                        <div class="input-group custom-input">

                                            <span class="input-group-text">
                                                <i class="bi bi-envelope"></i>
                                            </span>

                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control"
                                                placeholder="example@college.com"
                                                required
                                            >

                                        </div>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Password
                                        </label>

                                        <div class="input-group custom-input">

                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>

                                            <input
                                                type="password"
                                                name="password"
                                                id="password"
                                                class="form-control"
                                                placeholder="Enter your password"
                                                required
                                            >

                                            <button
                                                type="button"
                                                class="btn password-toggle"
                                                onclick="togglePassword()"
                                            >
                                                <i
                                                    id="eyeIcon"
                                                    class="bi bi-eye"
                                                ></i>
                                            </button>

                                        </div>

                                    </div>


                                    <div class="mb-4">

                                        <label class="form-label">
                                            Login As
                                        </label>

                                        <select
                                            name="role"
                                            class="form-select role-select"
                                            required
                                        >

                                            <option value="">
                                                Select your role
                                            </option>

                                            <option value="admin">
                                                Admin
                                            </option>

                                            <option value="faculty">
                                                Faculty
                                            </option>

                                            <option value="student">
                                                Student
                                            </option>

                                        </select>

                                    </div>


                                    <button
                                        type="submit"
                                        class="btn login-btn w-100"
                                    >
                                        Login
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </button>

                                </form>


                                <p class="text-center footer-text mt-4">
                                    College Management System
                                    <br>
                                    <span>Academic Project</span>
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

<script src="assets/js/script.js"></script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>