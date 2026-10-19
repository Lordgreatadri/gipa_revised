/**
 * Alpine.data components for the public and auth UI.
 *
 * Expressions in the templates are method/property references only so the
 * @alpinejs/csp build can run without 'unsafe-eval'. Inline assignment and
 * ternary expressions are not used in Blade.
 */

export function registerUi(Alpine) {
    Alpine.data('navMenu', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        get expanded() {
            return String(this.open);
        },
        get isClosed() {
            return !this.open;
        },
    }));

    Alpine.data('workspaceShell', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        get expanded() {
            return String(this.open);
        },
        get isClosed() {
            return !this.open;
        },
        get drawerClass() {
            return this.open ? 'is-open' : '';
        },
    }));

    Alpine.data('themeToggle', () => ({
        toggle() {
            this.$store.theme.toggle();
        },
        get pressed() {
            return String(this.$store.theme.isDark);
        },
    }));

    Alpine.data('passwordField', () => ({
        show: false,
        toggle() {
            this.show = !this.show;
        },
        get inputType() {
            return this.show ? 'text' : 'password';
        },
        get pressed() {
            return String(this.show);
        },
        get toggleLabel() {
            return this.show ? this.$root.dataset.hideLabel : this.$root.dataset.showLabel;
        },
        get eyeHidden() {
            return this.show;
        },
        get eyeOffHidden() {
            return !this.show;
        },
    }));

    Alpine.data('phoneInput', () => ({
        dial: '',
        init() {
            this.dial = this.$root.dataset.dial || '';
        },
    }));

    Alpine.data('registerForm', () => ({
        country: '',
        phoneCountry: '',
        phoneTouched: false,
        init() {
            const root = this.$root;

            this.country = root.dataset.country || '';
            this.phoneCountry = root.dataset.phoneCountry || this.country;
            this.phoneTouched = root.dataset.phoneTouched === '1';

            this.$watch('country', (value) => {
                if (!this.phoneTouched && value) {
                    this.phoneCountry = value;
                }
            });
        },
        onCountryChanged(event) {
            if (event.target.closest('[data-phone-input]')) {
                this.phoneTouched = true;
            }
        },
    }));

    Alpine.data('sectorPicker', () => ({
        catalogue: { subSectors: [], selectedSub: '' },
        init() {
            const node = document.getElementById('sector-catalogue');

            if (node) {
                try {
                    this.catalogue = JSON.parse(node.textContent || '{}');
                } catch {
                    this.catalogue = { subSectors: [], selectedSub: '' };
                }
            }

            this.filterSubSectors();
        },
        filterSubSectors() {
            const sector = this.$root.querySelector('[data-sector-select]');
            const sub = this.$root.querySelector('[data-subsector-select]');

            if (!sector || !sub) {
                return;
            }

            const selected = sub.value || this.catalogue.selectedSub || '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Choose a sub-sector';
            sub.replaceChildren(placeholder);

            (this.catalogue.subSectors || []).forEach((row) => {
                if (row.sector !== sector.value) {
                    return;
                }

                const option = document.createElement('option');
                option.value = row.uuid;
                option.textContent = row.name;
                option.selected = row.uuid === selected;
                sub.append(option);
            });
        },
    }));

    Alpine.data('investorFilters', () => ({
        data: { districts: [], subSectors: [], selectedDistrict: '', selectedSub: '' },
        init() {
            const node = document.getElementById('investor-filter-data');

            if (node) {
                try {
                    this.data = JSON.parse(node.textContent || '{}');
                } catch {
                    this.data = { districts: [], subSectors: [], selectedDistrict: '', selectedSub: '' };
                }
            }

            this.filterPlaces();
            this.filterSubSectors();
        },
        filterPlaces() {
            this.fillSelect('[data-district-select]', this.data.districts || [], this.regionValue(), 'region', this.data.selectedDistrict, 'All districts');
        },
        filterSubSectors() {
            this.fillSelect('[data-subsector-select]', this.data.subSectors || [], this.sectorValue(), 'sector', this.data.selectedSub, 'All sub-sectors');
        },
        regionValue() {
            return this.$root.querySelector('[data-region-select]')?.value || '';
        },
        sectorValue() {
            return this.$root.querySelector('[data-sector-select]')?.value || '';
        },
        fillSelect(selector, rows, parent, key, selected, placeholderText) {
            const select = this.$root.querySelector(selector);

            if (!select) {
                return;
            }

            const current = select.value || selected || '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderText;
            select.replaceChildren(placeholder);

            rows.forEach((row) => {
                if (parent && row[key] !== parent) {
                    return;
                }

                const option = document.createElement('option');
                option.value = row.uuid;
                option.textContent = row.name;
                option.selected = row.uuid === current;
                select.append(option);
            });
        },
    }));
}
