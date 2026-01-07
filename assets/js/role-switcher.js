/**
 * Role Switcher Component
 * Allows admin users to switch between their admin role and user role
 */

class RoleSwitcher {
    constructor() {
        this.currentUser = null;
        this.isExpanded = false;
        this.init();
    }

    async init() {
        // Check if user is logged in and has admin role
        const sessionData = await this.checkSession();
        if (!sessionData || !sessionData.logged_in) {
            return;
        }

        this.currentUser = sessionData.user;

        // Only show role switcher if user has an admin role (original role, not active role)
        if (this.hasAdminRole(this.currentUser.role)) {
            this.renderRoleSwitcher();
        }
    }

    hasAdminRole(role) {
        const adminRoles = [
            'admin',
            'unit_aduan_dalaman',
            'unit_aset',
            'bahagian_pentadbiran_kewangan',
            'unit_it_sokongan'
        ];
        return adminRoles.includes(role);
    }

    async checkSession() {
        try {
            const response = await fetch('/helpdesk/api/check_session.php');
            const data = await response.json();
            return data.data;
        } catch (error) {
            console.error('Error checking session:', error);
            return null;
        }
    }

    getRedirectUrl(role) {
        // Define where to redirect based on the role
        const redirectUrls = {
            'user': '/helpdesk/semakan.html',
            'admin': '/helpdesk/admin/index.php',
            'unit_aduan_dalaman': '/helpdesk/admin/unit-aduan-dalaman/index.php',
            'unit_aset': '/helpdesk/admin/unit-aset/index.php',
            'bahagian_pentadbiran_kewangan': '/helpdesk/admin/bahagian-pentadbiran-kewangan/index.php',
            'unit_it_sokongan': '/helpdesk/admin/unit-it-sokongan/index.php'
        };
        return redirectUrls[role] || '/helpdesk/semakan.html';
    }

    renderRoleSwitcher() {
        // Create compact role switcher button
        const container = document.createElement('div');
        container.id = 'role-switcher-container';
        container.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 9999;
        `;

        // Get role display names
        const activeRole = this.currentUser.active_role || this.currentUser.role;
        const originalRole = this.currentUser.role;

        const roleNames = {
            'user': 'Pengguna',
            'admin': 'Pentadbir',
            'unit_aduan_dalaman': 'Unit Aduan Dalaman',
            'unit_aset': 'Unit Aset',
            'bahagian_pentadbiran_kewangan': 'Bahagian Pentadbiran & Kewangan',
            'unit_it_sokongan': 'Unit IT / Sokongan'
        };

        // Determine button color based on active role
        const buttonColor = activeRole === 'user' ? '#007bff' : '#28a745';
        const buttonIcon = activeRole === 'user' ? '👤' : '⚙️';

        container.innerHTML = `
            <!-- Compact toggle button -->
            <button id="role-switcher-toggle" style="
                padding: 12px 20px;
                background: ${buttonColor};
                color: white;
                border: none;
                border-radius: 25px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
            ">
                <span>${buttonIcon}</span>
                <span id="current-role-text">${roleNames[activeRole]}</span>
                <span style="font-size: 10px;">▼</span>
            </button>

            <!-- Expandable panel -->
            <div id="role-switcher-panel" style="
                position: absolute;
                bottom: 60px;
                left: 0;
                background: white;
                padding: 15px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                border: 1px solid #e0e0e0;
                min-width: 280px;
                display: none;
                animation: slideUp 0.3s ease;
            ">
                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e0e0e0;">
                    <div style="font-size: 15px; font-weight: 600; color: #333; margin-bottom: 8px;">Tukar Peranan</div>
                    <div style="font-size: 12px; color: #666;">
                        <div style="margin-bottom: 4px;"><strong>Peranan Asal:</strong> ${roleNames[originalRole]}</div>
                        <div><strong>Peranan Aktif:</strong> <span id="active-role-display">${roleNames[activeRole]}</span></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button id="switch-to-user" class="role-switch-btn" style="
                        padding: 10px 16px;
                        border: 1px solid #007bff;
                        background: ${activeRole === 'user' ? '#e7f3ff' : '#007bff'};
                        color: ${activeRole === 'user' ? '#007bff' : 'white'};
                        border-radius: 6px;
                        cursor: ${activeRole === 'user' ? 'not-allowed' : 'pointer'};
                        font-size: 14px;
                        font-weight: 500;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.2s ease;
                        opacity: ${activeRole === 'user' ? '0.6' : '1'};
                    " ${activeRole === 'user' ? 'disabled' : ''}>
                        <span>👤</span>
                        <span>Mod Pengguna</span>
                        ${activeRole === 'user' ? '<span style="font-size: 12px;">✓</span>' : ''}
                    </button>

                    <button id="switch-to-admin" class="role-switch-btn" style="
                        padding: 10px 16px;
                        border: 1px solid #28a745;
                        background: ${activeRole !== 'user' ? '#e8f5e9' : '#28a745'};
                        color: ${activeRole !== 'user' ? '#28a745' : 'white'};
                        border-radius: 6px;
                        cursor: ${activeRole !== 'user' ? 'not-allowed' : 'pointer'};
                        font-size: 14px;
                        font-weight: 500;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.2s ease;
                        opacity: ${activeRole !== 'user' ? '0.6' : '1'};
                    " ${activeRole !== 'user' ? 'disabled' : ''}>
                        <span>⚙️</span>
                        <span>Mod ${roleNames[originalRole]}</span>
                        ${activeRole !== 'user' ? '<span style="font-size: 12px;">✓</span>' : ''}
                    </button>
                </div>

                <div id="switch-status" style="
                    margin-top: 12px;
                    padding: 8px;
                    font-size: 12px;
                    text-align: center;
                    border-radius: 4px;
                    display: none;
                "></div>
            </div>
        `;

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            #role-switcher-toggle:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            }
            .role-switch-btn:not(:disabled):hover {
                transform: scale(1.02);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(container);

        // Toggle panel visibility
        const toggleBtn = document.getElementById('role-switcher-toggle');
        const panel = document.getElementById('role-switcher-panel');

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.isExpanded = !this.isExpanded;
            panel.style.display = this.isExpanded ? 'block' : 'none';
        });

        // Close panel when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                this.isExpanded = false;
                panel.style.display = 'none';
            }
        });

        // Add event listeners for switching roles
        document.getElementById('switch-to-user').addEventListener('click', () => this.switchRole('user'));
        document.getElementById('switch-to-admin').addEventListener('click', () => this.switchRole(originalRole));
    }

    async switchRole(newRole) {
        const statusDiv = document.getElementById('switch-status');
        statusDiv.style.display = 'block';
        statusDiv.style.background = '#f0f0f0';
        statusDiv.style.color = '#666';
        statusDiv.textContent = 'Menukar peranan...';

        try {
            const response = await fetch('/helpdesk/api/switch_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ role: newRole })
            });

            const result = await response.json();

            if (result.success) {
                statusDiv.style.background = '#d4edda';
                statusDiv.style.color = '#155724';
                statusDiv.textContent = 'Peranan berjaya ditukar! Mengalih ke halaman...';

                // Redirect to appropriate page based on the new role
                const redirectUrl = this.getRedirectUrl(newRole);

                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 800);
            } else {
                statusDiv.style.background = '#f8d7da';
                statusDiv.style.color = '#721c24';
                statusDiv.textContent = result.message || 'Ralat menukar peranan';
            }
        } catch (error) {
            console.error('Error switching role:', error);
            statusDiv.style.background = '#f8d7da';
            statusDiv.style.color = '#721c24';
            statusDiv.textContent = 'Ralat sistem. Sila cuba lagi.';
        }
    }
}

// Initialize role switcher when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new RoleSwitcher();
    });
} else {
    new RoleSwitcher();
}
