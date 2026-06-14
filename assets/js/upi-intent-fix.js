/**
 * UPI Intent URL Fix v2.0
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
 * Include this script on ANY page with UPI links (checkout, order, admin):
 *   <script src="/assets/js/upi-intent-fix.js"></script>
 * 
 * It auto-intercepts all UPI link clicks and fixes them.
 * 
 * To show a TEST panel in admin:
 *   UPIIntentFix.showTestPanel({ pa: 'your@upi', pn: 'Name' });
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

        /**
         * Show a "Test UPI Intent" panel.
         * Call this from admin pages to let the store owner test payments.
         * 
         * @param {Object} config
         * @param {string} config.pa - UPI ID to test
         * @param {string} config.pn - Payee name
         * @param {string} [config.containerId] - DOM element ID to inject panel into (creates one if missing)
         */
        showTestPanel: function(config) {
            var self = this;
            var pa = config.pa || '';
            var pn = config.pn || '';
            var containerId = config.containerId || 'upi-test-panel';

            var container = document.getElementById(containerId);
            if (!container) {
                container = document.createElement('div');
                container.id = containerId;
                // Find a good place to inject - after main content or at end of admin-main
                var adminMain = document.querySelector('.admin-main') || document.querySelector('main') || document.body;
                adminMain.appendChild(container);
            }

            container.innerHTML = '' +
                '<div style="margin-top:30px;padding:24px;background:#f8fafc;border:2px solid #2874f0;border-radius:12px;">' +
                '  <h2 style="margin:0 0 8px;font-size:1.2em;color:#1e293b;">🔗 Test UPI Intent</h2>' +
                '  <p style="color:#64748b;font-size:0.9em;margin-bottom:16px;">Test if payment goes through without risk policy errors. Enter ₹1 to test.</p>' +
                '  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:16px;">' +
                '    <div>' +
                '      <label style="display:block;font-size:0.8em;color:#555;margin-bottom:4px;">UPI ID</label>' +
                '      <input type="text" id="upi-test-pa" value="' + pa + '" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;width:220px;" readonly>' +
                '    </div>' +
                '    <div>' +
                '      <label style="display:block;font-size:0.8em;color:#555;margin-bottom:4px;">Amount (₹)</label>' +
                '      <input type="number" id="upi-test-amount" value="1" min="1" step="0.01" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;width:100px;">' +
                '    </div>' +
                '    <button id="upi-test-generate" style="padding:10px 20px;background:#2874f0;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:500;">Generate & Test</button>' +
                '  </div>' +
                '  <div id="upi-test-result" style="display:none;">' +
                '    <div style="margin-bottom:12px;">' +
                '      <label style="display:block;font-size:0.8em;color:#555;margin-bottom:4px;">Generated URL (unique tr each time):</label>' +
                '      <code id="upi-test-url" style="display:block;padding:10px;background:#1e293b;color:#4ade80;border-radius:6px;font-size:0.8em;word-break:break-all;overflow-x:auto;"></code>' +
                '    </div>' +
                '    <div style="display:flex;flex-wrap:wrap;gap:8px;">' +
                '      <a id="upi-test-open" href="#" style="display:inline-block;padding:10px 20px;background:#388e3c;color:#fff;border-radius:6px;text-decoration:none;font-weight:500;">Open UPI App</a>' +
                '      <a id="upi-test-gpay" href="#" style="display:inline-block;padding:10px 20px;background:#4285F4;color:#fff;border-radius:6px;text-decoration:none;font-weight:500;">Google Pay</a>' +
                '      <a id="upi-test-phonepe" href="#" style="display:inline-block;padding:10px 20px;background:#5f259f;color:#fff;border-radius:6px;text-decoration:none;font-weight:500;">PhonePe</a>' +
                '      <a id="upi-test-paytm" href="#" style="display:inline-block;padding:10px 20px;background:#00BAF2;color:#fff;border-radius:6px;text-decoration:none;font-weight:500;">Paytm</a>' +
                '    </div>' +
                '    <p id="upi-test-info" style="margin-top:10px;font-size:0.8em;color:#666;"></p>' +
                '  </div>' +
                '</div>';

            // Generate button handler
            document.getElementById('upi-test-generate').addEventListener('click', function() {
                var testPa = document.getElementById('upi-test-pa').value;
                var testAm = document.getElementById('upi-test-amount').value;
                var tr = self.generateTr();
                var tn = 'Test payment to ' + pn;

                var url = self.buildUrl({ pa: testPa, pn: pn, am: testAm, tn: tn, tr: tr, scheme: 'upi' });

                document.getElementById('upi-test-url').textContent = url;
                document.getElementById('upi-test-open').href = url;
                document.getElementById('upi-test-gpay').href = url; // GPay uses upi://
                document.getElementById('upi-test-phonepe').href = url.replace('upi://pay?', 'phonepe://pay?');
                document.getElementById('upi-test-paytm').href = url.replace('upi://pay?', 'paytmmp://pay?');
                document.getElementById('upi-test-result').style.display = 'block';
                document.getElementById('upi-test-info').textContent = 'TR: ' + tr + ' | Generated at: ' + new Date().toLocaleTimeString();
            });

            // Make app buttons regenerate tr on click too
            ['upi-test-open', 'upi-test-gpay', 'upi-test-phonepe', 'upi-test-paytm'].forEach(function(id) {
                document.getElementById(id).addEventListener('click', function(e) {
                    e.preventDefault();
                    var testPa = document.getElementById('upi-test-pa').value;
                    var testAm = document.getElementById('upi-test-amount').value;
                    var freshTr = self.generateTr();
                    var tn = 'Test payment to ' + pn;
                    var scheme = 'upi';
                    if (id.indexOf('phonepe') > -1) scheme = 'phonepe';
                    if (id.indexOf('paytm') > -1) scheme = 'paytmmp';
                    
                    var freshUrl = self.buildUrl({ pa: testPa, pn: pn, am: testAm, tn: tn, tr: freshTr, scheme: scheme });
                    document.getElementById('upi-test-info').textContent = 'Fresh TR: ' + freshTr + ' | ' + new Date().toLocaleTimeString();
                    window.location.href = freshUrl;
                });
            });
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
