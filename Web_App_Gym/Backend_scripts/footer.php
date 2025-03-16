<style>
   /* Footer Styles */
    footer {
        background: #333;
        color: #fff;
        padding: 20px 10px;
        text-align: center;
        box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .footer-links {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
    }

    .footer-links a {
        text-decoration: none;
        color: #ddd;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: #5A8DEE;
    }

    footer p {
        font-size: 0.85rem;
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .footer-container {
            padding: 0 10px;
        }

        .footer-links {
            flex-direction: column;
            gap: 8px;
        }

        footer p {
            margin-top: 10px;
        }
    }

</style>

<footer>
    <div class="footer-container">
        <div class="footer-links">
            <a href="privacy.html">Privacy Policy</a>
            <a href="terms.html">Terms of Service</a>
            <a href="faq.html">FAQ</a>
        </div>
        <div class="footer-links">
            <a href="mailto:info@gymmanagement.com">Email Us: info@gymmanagement.com</a>
            <a href="tel:+1234567890">Call Us: +1 234-567-890</a>
            <a href="https://www.google.com/maps/place/Inferno_Gym" target="_blank">Find Us on Google Maps</a>
        </div>
        <p>&copy; <?php echo date("Y"); ?> Gym Management. All rights reserved.</p>
    </div>
</footer>

