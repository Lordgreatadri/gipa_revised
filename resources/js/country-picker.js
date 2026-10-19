/**
 * Accessible combobox for choosing a country. The same component drives the
 * residence-country field (label = country name) and the phone dial-code
 * field (label = dial code). Options are provided by the server as
 * [{ value, name, code, flag }] on the root element's data-countries attribute.
 *
 * Template bindings are method/property references so this runs under the
 * Alpine CSP build (no 'unsafe-eval', no inline style from x-show).
 */

export function registerCountryPicker(Alpine) {
    Alpine.data('countryPicker', () => ({
        open: false,
        query: '',
        active: -1,
        value: '',
        display: 'name',
        countries: [],

        init() {
            this.display = this.$root.dataset.display || 'name';
            this.value = this.$root.dataset.initial || '';

            try {
                this.countries = JSON.parse(this.$root.dataset.countries || '[]');
            } catch {
                this.countries = [];
            }
        },

        get selected() {
            return this.countries.find((c) => c.value === this.value) || null;
        },

        get hasSelection() {
            return this.selected !== null;
        },

        get hasSelectedFlag() {
            return Boolean(this.selected?.flag);
        },

        get selectedFlagHidden() {
            return !this.hasSelectedFlag;
        },

        get selectedAbbrevHidden() {
            return this.hasSelectedFlag;
        },

        get panelHidden() {
            return !this.open;
        },

        get stackClass() {
            return this.open ? 'z-50' : '';
        },

        get expanded() {
            return String(this.open);
        },

        get filtered() {
            const q = this.query.trim().toLowerCase();

            if (q === '') {
                return this.countries;
            }

            return this.countries.filter((c) => {
                const abbreviation = (c.abbreviation || '').toLowerCase();

                return (
                    c.name.toLowerCase().includes(q) ||
                    abbreviation === q ||
                    abbreviation.startsWith(q) ||
                    c.code.replace('+', '').startsWith(q.replace('+', ''))
                );
            });
        },

        get hasMatches() {
            return this.filtered.length > 0;
        },

        get noSelection() {
            return this.selected === null;
        },

        get selectedLabel() {
            return this.label(this.selected);
        },

        label(country) {
            if (!country) {
                return '';
            }

            return this.display === 'code' ? country.code : country.name;
        },

        flagHidden(country) {
            return !country?.flag;
        },

        abbrevHidden(country) {
            return Boolean(country?.flag);
        },

        optionSelected(country) {
            return String(country.value === this.value);
        },

        optionActive(country) {
            return String(this.isActive(country));
        },

        optionClass(country) {
            const classes = [];

            if (this.isActive(country)) {
                classes.push('bg-brand-50', 'dark:bg-brand-900/40');
            }

            if (country.value === this.value) {
                classes.push('font-semibold');
            }

            return classes.join(' ');
        },

        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            this.open = true;
            this.query = '';
            this.active = Math.max(
                0,
                this.filtered.findIndex((c) => c.value === this.value),
            );
            this.$nextTick(() => {
                this.$refs.search?.focus();
                this.scrollActiveIntoView();
            });
        },

        close() {
            if (!this.open) {
                return;
            }

            this.open = false;
            this.query = '';
            this.$refs.trigger?.focus();
        },

        dismiss() {
            if (!this.open) {
                return;
            }

            this.open = false;
            this.query = '';
        },

        onFocusOut(event) {
            if (!this.$el.contains(event.relatedTarget)) {
                this.dismiss();
            }
        },

        resetActive() {
            this.active = 0;
        },

        setActive(country) {
            this.active = this.filtered.indexOf(country);
        },

        choose(country) {
            this.value = country.value;
            this.close();
            this.$dispatch('country-changed', country);
        },

        moveDown() {
            this.move(1);
        },

        moveUp() {
            this.move(-1);
        },

        move(delta) {
            const count = this.filtered.length;

            if (count === 0) {
                return;
            }

            this.active = (this.active + delta + count) % count;
            this.scrollActiveIntoView();
        },

        chooseActive() {
            const country = this.filtered[this.active];

            if (country) {
                this.choose(country);
            }
        },

        isActive(country) {
            return this.filtered[this.active]?.value === country.value;
        },

        scrollActiveIntoView() {
            this.$nextTick(() => {
                const el = this.$refs.list?.querySelector('[data-active="true"]');
                el?.scrollIntoView({ block: 'nearest' });
            });
        },
    }));
}
