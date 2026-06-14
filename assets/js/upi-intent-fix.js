/**
 * UPI Intent URL Fix
 * 
 * This script fixes UPI payment intent URLs to avoid "Risk Threshold Exceeded"
 * and "Transaction may be risky" errors in Google Pay, PhonePe, Paytm, etc.
 * 
 * THE PROBLEM:
 * - Missing or reused `tr` (transaction reference) parameter
 * - Missing `tn` (transaction note) parameter
 * - Missing `mode` and `orgid` parameters
 * 
 * THE FIX:
 * - Generates a UNIQUE `tr` on every single button click
 * - Adds `tn`, `mode=00`, `orgid=000000` parameters
 * - Properly URL-encodes all values
 * 
 * USAGE:
 * Include this script on your checkout/order page AFTER your UPI buttons are rendered.
 * It will automatically intercept clicks on any link with href starting with "upi://pay"
 * and fix the URL before the UPI app opens.
 * 
 * Or call UPIIntentFix.buildUrl() directly to generate proper URLs.
 */

(function() {
    'use strict';

    var UPIIntentFix = {
        /**
         * Generate a unique transaction reference ID.
         * Uses timestamp + random string to ensure uniqueness per click.
         */
        generateTr: function() {
            var timestamp = Date.now().toString(36).toUpperCase();
            var random = Math.random().toString(36).substring(2, 10).toUpperCase();
            return 'TXN' + timestamp + random;
        },

        /**
         * Generate a unique transaction ID
         */
        generateTid: function() {
            return Date.now().toString() + Math.floor(Math.random() * 10000).toString();
        },

        /**
         * Build a proper UPI intent URL with all required parameters.
         * 
         * @param {Object} params - Payment parameters
         * @param {string} params.pa - Payee VPA/UPI ID (required)
         * @param {string} params.pn - Payee name (required)
         * @param {string} [params.am] - Amount (optional)
         * @param {string} [params.cu] - Currency code (default: INR)
         * @param {string} [params.tn] - Transaction note
         * @param {string} [params.tr] - Transaction reference (auto-generated if empty)
         * @param {string} [params.mc] - Merchant code (optional)
         * @param {string} [params.mode] - Payment mode (default: 00 for P2P)
         * @param {string} [params.scheme] - URL scheme (default: upi, alternatives: phonepe, paytmmp)
         * @returns {string|null} - UPI intent URL or null if pa is missing
         */
        buildUrl: function(params) {
            var pa = params.pa || '';
            var pn = params.pn || '';
            var am = params.am || '';
            var cu = params.cu || 'INR';
            var tn = params.tn || '';
            var tr = params.tr || this.generateTr();
            var mc = params.mc || '';
            var mode = params.mode || '00';
            var scheme = params.scheme || 'upi';

            if (!pa) return null;

            // Build name from VPA if not provided
            if (!pn) pn = pa.split('@')[0];

            var queryParts = [];
            queryParts.push('pa=' + encodeURIComponent(pa));
            queryParts.push('pn=' + encodeURIComponent(pn));
            queryParts.push('cu=' + encodeURIComponent(cu));

            // Transaction reference - MUST be unique per attempt
            queryParts.push('tr=' + encodeURIComponent(tr));

            // Transaction note - helps legitimize the payment
            if (!tn) {
                tn = 'Payment to ' + pn;
            }
            queryParts.push('tn=' + encodeURIComponent(tn));

            // Amount (properly formatted)
            if (am && !isNaN(parseFloat(am)) && parseFloat(am) > 0) {
                queryParts.push('am=' + parseFloat(am).toFixed(2));
            }

            // Merchant code (if available)
            if (mc) {
                queryParts.push('mc=' + encodeURIComponent(mc));
            }

            // Payment mode (00 = P2P default)
            queryParts.push('mode=' + encodeURIComponent(mode));

            // NPCI org ID
            queryParts.push('orgid=000000');

            return scheme + '://pay?' + queryParts.join('&');
        },

        /**
         * Fix an existing UPI URL by adding/replacing tr and other missing params.
         * 
         * @param {string} existingUrl - The current upi://pay URL
         * @returns {string} - Fixed URL with fresh tr
         */
        fixUrl: function(existingUrl) {
            if (!existingUrl || existingUrl.indexOf('://pay') === -1) return existingUrl;

            // Determine the scheme (upi, phonepe, paytmmp, etc.)
            var scheme = existingUrl.split('://pay')[0] || 'upi';

            // Parse existing parameters
            var queryString = existingUrl.split('?')[1] || '';
            var params = {};
            queryString.split('&').forEach(function(pair) {
                var parts = pair.split('=');
                if (parts[0]) {
                    params[parts[0]] = decodeURIComponent(parts[1] || '');
                }
            });

            // Rebuild with fresh tr and all required params
            return this.buildUrl({
                pa: params.pa || '',
                pn: params.pn || '',
                am: params.am || '',
                cu: params.cu || 'INR',
                tn: params.tn || '',
                tr: this.generateTr(), // ALWAYS fresh
                mc: params.mc || '',
                mode: params.mode || '00',
                scheme: scheme
            });
        },

        /**
         * Auto-fix all UPI links on the page.
         * Intercepts clicks on any link with href containing "://pay?" 
         * and regenerates the URL with a fresh transaction reference.
         */
        autoFix: function() {
            var self = this;

            document.addEventListener('click', function(e) {
                var target = e.target;
                
                // Walk up to find the <a> tag
                while (target && target.tagName !== 'A') {
                    target = target.parentElement;
                }

                if (!target || !target.href) return;

                var href = target.getAttribute('href') || target.href || '';

                // Check if it's a UPI intent link
                if (href.indexOf('://pay?') > -1 || href.indexOf('://pay?') > -1) {
                    e.preventDefault();
                    
                    // Fix the URL with fresh tr
                    var fixedUrl = self.fixUrl(href);
                    
                    if (fixedUrl) {
                        console.log('[UPI Fix] Original:', href.substring(0, 80) + '...');
                        console.log('[UPI Fix] Fixed:', fixedUrl.substring(0, 80) + '...');
                        console.log('[UPI Fix] Fresh TR generated');
                        
                        // Navigate to the fixed URL
                        window.location.href = fixedUrl;
                    }
                }
            }, true); // Use capture phase to intercept before other handlers

            console.log('[UPI Intent Fix] Active - All UPI links will get fresh transaction references on click');
        },

        /**
         * Initialize with specific UPI account details.
         * Call this to set up UPI buttons with the correct payee info.
         * 
         * @param {Object} config
         * @param {string} config.pa - Your UPI ID
         * @param {string} config.pn - Your name
         * @param {string} [config.noteTemplate] - Note template with {reference} placeholder
         */
        init: function(config) {
            this.config = config || {};
            this.autoFix();
        }
    };

    // Expose globally
    window.UPIIntentFix = UPIIntentFix;

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            UPIIntentFix.autoFix();
        });
    } else {
        UPIIntentFix.autoFix();
    }
})();
