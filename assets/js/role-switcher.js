/**
 * Role Switcher Component
 * Allows admin users to switch between their admin role and user role
 */

class RoleSwitcher {
    constructor() {
        this.currentUser = null;
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

    renderRoleSwitcher() {
        // Create role switcher container
        const container = document.createElement('div');
        container.id = 'role-switcher-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
            min-width: 250px;
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

        container.innerHTML = `
            <div style="margin-bottom: 10px;">
                <strong style="font-size: 14px; color: #333;">Tukar Peranan</strong>
            </div>
            <div style="margin-bottom: 10px; font-size: 13px; color: #666;">
                <div><strong>Peranan Asal:</strong> ${roleNames[originalRole]}</div>
                <div><strong>Peranan Aktif:</strong> <span id="active-role-display">${roleNames[activeRole]}</span></div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button id="switch-to-user" class="role-switch-btn" style="flex: 1; padding: 8px 12px; border: 1px solid #007bff; background: #007bff; color: white; border-radius: 4px; cursor: pointer; font-size: 13px;">
                    Mod Pengguna
                </button>
                <button id="switch-to-admin" class="role-switch-btn" style="flex: 1; padding: 8px 12px; border: 1px solid #28a745; background: #28a745; color: white; border-radius: 4px; cursor: pointer; font-size: 13px;">
                    Mod ${roleNames[originalRole]}
                </button>
            </div>
            <div id="switch-status" style="margin-top: 10px; font-size: 12px; text-align: center; display: none;"></div>
        `;

        document.body.appendChild(container);

        // Add event listeners
        document.getElementById('switch-to-user').addEventListener('click', () => this.switchRole('user'));
        document.getElementById('switch-to-admin').addEventListener('click', () => this.switchRole(originalRole));

        // Update button states based on active role
        this.updateButtonStates(activeRole, originalRole);
    }

    updateButtonStates(activeRole, originalRole) {
        const userBtn = document.getElementById('switch-to-user');
        const adminBtn = document.getElementById('switch-to-admin');

        if (activeRole === 'user') {
            userBtn.disabled = true;
            userBtn.style.opacity = '0.5';
            userBtn.style.cursor = 'not-allowed';
            adminBtn.disabled = false;
            adminBtn.style.opacity = '1';
            adminBtn.style.cursor = 'pointer';
        } else {
            userBtn.disabled = false;
            userBtn.style.opacity = '1';
            userBtn.style.cursor = 'pointer';
            adminBtn.disabled = true;
            adminBtn.style.opacity = '0.5';
            adminBtn.style.cursor = 'not-allowed';
        }
    }

    async switchRole(newRole) {
        const statusDiv = document.getElementById('switch-status');
        statusDiv.style.display = 'block';
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
                statusDiv.style.color = '#28a745';
                statusDiv.textContent = 'Peranan berjaya ditukar! Memuat semula halaman...';

                // Update active role display
                const roleNames = {
                    'user': 'Pengguna',
                    'admin': 'Pentadbir',
                    'unit_aduan_dalaman': 'Unit Aduan Dalaman',
                    'unit_aset': 'Unit Aset',
                    'bahagian_pentadbiran_kewangan': 'Bahagian Pentadbiran & Kewangan',
                    'unit_it_sokongan': 'Unit IT / Sokongan'
                };

                document.getElementById('active-role-display').textContent = roleNames[newRole];

                // Reload page after a short delay to reflect the new role
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                statusDiv.style.color = '#dc3545';
                statusDiv.textContent = result.message || 'Ralat menukar peranan';
            }
        } catch (error) {
            console.error('Error switching role:', error);
            statusDiv.style.color = '#dc3545';
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
