<?php // user_navbar.php as an include file for PHP pages ?>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    body {
        background: #f4f7fa;
        margin: 0;
        padding: 0;
        opacity: 0; /* Hide initially to prevent FOUC */
        transition: opacity 0.3s ease;
        overflow-x: hidden; /* Prevent horizontal scroll */
    }

    body.loaded {
        opacity: 1;
    }

    nav {
        background: #1e3a8a;
        box-shadow: 0 4px 16px rgba(30, 58, 138, 0.08);
        padding: 0.8rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        height: 60px;
    }

    .main-content {
        margin-top: 70px;
        padding: 20px;
        width: 100%;
    }

    .navbar-logo {
        display: flex;
        align-items: center;
        font-size: 1.7rem;
        font-weight: bold;
        color: #fff;
        letter-spacing: 2px;
        text-shadow: 0 2px 8px rgba(30, 64, 175, 0.12);
        margin-right: auto;
        z-index: 1001; /* Higher than nav */
    }

    .navbar-logo img {
        height: 32px;
        width: 32px;
        margin-right: 10px;
        object-fit: cover;
        border-radius: 50%;
        display: inline-block;
    }

    .nav-links {
        display: flex;
        list-style: none;
        gap: 1.2rem;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .nav-links a {
        text-decoration: none;
        color: #e0e7ef;
        font-weight: 500;
        padding: 0.5rem 1.2rem;
        border-radius: 8px;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        font-size: 1.05rem;
    }

    .nav-links a:hover {
        background: #fff;
        color: #2563eb;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.08);
    }

    .logout-btn {
        background: #fff !important;
        border: none;
        color: #2563eb !important;
        font-weight: 600;
        padding: 0.5rem 1.2rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.05rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.08);
        transition: background 0.2s, color 0.2s;
    }

    .logout-btn:hover {
        background: #2563eb !important;
        color: #fff !important;
    }

    .burger {
        display: none;
        cursor: pointer;
        z-index: 1001; /* Higher than nav */
    }

    @media screen and (max-width: 768px) {
        nav {
            padding: 0.8rem 1rem;
        }
        
        .nav-links {
            position: fixed;
            top: 60px;
            left: 0;
            width: 100%;
            background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);
            flex-direction: column;
            padding: 1rem 0;
            box-shadow: 0 5px 16px rgba(30, 58, 138, 0.13);
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .nav-links.active {
            transform: translateX(0);
        }

        .nav-links li {
            width: 100%;
            text-align: center;
        }

        .nav-links a {
            width: 90%;
            margin: 0.5rem auto;
            display: block;
        }
        .logout-btn {
            width: auto !important;
            min-width: 0;
            max-width: 100vw;
            margin: 0.5rem auto;
            display: inline-block;
            padding-left: 1.2rem;
            padding-right: 1.2rem;
        }
        .burger {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            position: relative; /* Ensure it stays on top */
        }

        .burger div {
            width: 26px;
            height: 3.5px;
            background-color: #fff;
            margin: 4px 0;
            border-radius: 2px;
            transition: all 0.3s cubic-bezier(.68,-0.55,.27,1.55);
        }

        .burger.active div:nth-child(1) {
            transform: rotate(-45deg) translate(-6px, 7px);
        }

        .burger.active div:nth-child(2) {
            opacity: 0;
        }

        .burger.active div:nth-child(3) {
            transform: rotate(45deg) translate(-6px, -7px);
        }
    }
</style>

<nav>
    <div class="navbar-logo">
        <img src="img/aski_logo.jpg" alt="Logo">
        Health<span style="color:#a5b4fc;">Record</span>
    </div>
    <ul class="nav-links">
        <li><a href="landing.php">Home</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><button class="logout-btn" id="logoutBtn">Logout</button></li>
    </ul>
    <div class="burger">
        <div></div>
        <div></div>
        <div></div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('loaded');
        
        const burger = document.querySelector('.burger');
        const navLinks = document.querySelector('.nav-links');

        burger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            burger.classList.toggle('active');
            
            // Prevent body scroll when menu is open
            if (navLinks.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        document.querySelectorAll('.nav-links a, .logout-btn').forEach(item => {
            item.addEventListener('click', () => {
                navLinks.classList.remove('active');
                burger.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'user_login.php';
            }
        });
    });
</script>