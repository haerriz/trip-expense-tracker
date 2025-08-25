// PWA Install Functionality
let deferredPrompt;
let installButton;

// Listen for the beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA install prompt available');
    
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    
    // Show install button
    showInstallButton();
});

// Show install button
function showInstallButton() {
    // Create install button if it doesn't exist
    if (!installButton) {
        installButton = document.createElement('button');
        installButton.innerHTML = '<i class="material-icons left">get_app</i>Install App';
        installButton.className = 'btn waves-effect waves-light green install-btn';
        installButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        `;
        installButton.onclick = installPWA;
        document.body.appendChild(installButton);
        
        // Animate in
        setTimeout(() => {
            installButton.style.transform = 'translateY(0)';
            installButton.style.opacity = '1';
        }, 100);
    }
    
    installButton.style.display = 'block';
}

// Install PWA
async function installPWA() {
    if (!deferredPrompt) {
        console.log('No install prompt available');
        return;
    }
    
    // Show the install prompt
    deferredPrompt.prompt();
    
    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;
    
    console.log(`User response to the install prompt: ${outcome}`);
    
    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
        // Hide the install button
        hideInstallButton();
    }
    
    // Clear the deferredPrompt
    deferredPrompt = null;
}

// Hide install button
function hideInstallButton() {
    if (installButton) {
        installButton.style.transform = 'translateY(100px)';
        installButton.style.opacity = '0';
        setTimeout(() => {
            installButton.style.display = 'none';
        }, 300);
    }
}

// Listen for app installed event
window.addEventListener('appinstalled', (evt) => {
    console.log('PWA was installed');
    hideInstallButton();
    
    // Show success message
    if (typeof M !== 'undefined') {
        M.toast({
            html: '<i class="material-icons left">check_circle</i>App installed successfully!',
            classes: 'green',
            displayLength: 4000
        });
    }
});

// Check if app is already installed
function isAppInstalled() {
    // Check if running in standalone mode
    return window.matchMedia('(display-mode: standalone)').matches || 
           window.navigator.standalone === true;
}

// Hide install button if app is already installed
if (isAppInstalled()) {
    console.log('App is already installed');
} else {
    // Show install button after a delay if prompt hasn't appeared
    setTimeout(() => {
        if (!deferredPrompt && !isAppInstalled()) {
            // Create a manual install button for browsers that don't support beforeinstallprompt
            const manualInstallBtn = document.createElement('div');
            manualInstallBtn.innerHTML = `
                <div class="install-hint" style="
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #4CAF50;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 25px;
                    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
                    font-size: 14px;
                    z-index: 1000;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                ">
                    <i class="material-icons" style="font-size: 18px;">info</i>
                    <span>Add to Home Screen</span>
                    <i class="material-icons close-hint" style="font-size: 16px; margin-left: 8px;">close</i>
                </div>
            `;
            
            const hint = manualInstallBtn.querySelector('.install-hint');
            const closeBtn = manualInstallBtn.querySelector('.close-hint');
            
            closeBtn.onclick = (e) => {
                e.stopPropagation();
                hint.style.transform = 'translateY(100px)';
                hint.style.opacity = '0';
                setTimeout(() => hint.remove(), 300);
            };
            
            hint.onclick = () => {
                // Show instructions for manual installation
                if (typeof M !== 'undefined') {
                    M.toast({
                        html: 'Tap the browser menu and select "Add to Home Screen" to install the app',
                        displayLength: 6000
                    });
                }
            };
            
            document.body.appendChild(manualInstallBtn);
            
            // Auto-hide after 10 seconds
            setTimeout(() => {
                if (hint.parentNode) {
                    hint.style.transform = 'translateY(100px)';
                    hint.style.opacity = '0';
                    setTimeout(() => hint.remove(), 300);
                }
            }, 10000);
        }
    }, 5000);
}

// Initialize PWA features
document.addEventListener('DOMContentLoaded', () => {
    // Register service worker if available
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('Service Worker registered successfully:', registration);
            })
            .catch((error) => {
                console.log('Service Worker registration failed:', error);
            });
    }
});