// Check if class is already defined to prevent "Identifier has already been declared" error
if (typeof LocationManager === 'undefined') {

    class LocationManager {
        constructor(config) {
            // IDs of the dropdown elements
            this.divisionSelect = document.getElementById(config.divisionId);
            this.sectionSelect = document.getElementById(config.sectionId);
            this.unitSelect = document.getElementById(config.unitId);
            this.locationIdInput = document.getElementById(config.locationIdInput);

            // Data sources (Global variables from location_loader.php)
            // Use window.variable to ensure we access the global scope
            this.sectionsData = window.sectionsData || [];
            this.unitsData = window.unitsData || [];

            this.init();
        }

        init() {
            if (this.divisionSelect) {
                // Remove old listeners to prevent duplicates (optional but good practice)
                // cloneNode(true) is a quick hack to wipe listeners, but for now, simple add is fine
                this.divisionSelect.addEventListener('change', () => this.handleDivisionChange());
            }
            if (this.sectionSelect) {
                this.sectionSelect.addEventListener('change', () => this.handleSectionChange());
            }
            if (this.unitSelect) {
                this.unitSelect.addEventListener('change', () => this.calculateFinalId());
            }
        }

        handleDivisionChange() {
            const divisionId = this.divisionSelect.value;
            this.populateDropdown(this.sectionSelect, this.sectionsData, divisionId, 'Select Section');
            this.populateDropdown(this.unitSelect, this.unitsData, divisionId, 'Select Unit');
            this.calculateFinalId();
        }

        handleSectionChange() {
            const sectionId = this.sectionSelect.value;
            const divisionId = this.divisionSelect.value;
            const parentId = sectionId ? sectionId : divisionId;
            this.populateDropdown(this.unitSelect, this.unitsData, parentId, 'Select Unit');
            this.calculateFinalId();
        }

        populateDropdown(selectElement, dataArray, parentId, defaultText) {
            if (!selectElement) return;

            selectElement.innerHTML = `<option value="">${defaultText}</option>`;
            
            if (!parentId) {
                selectElement.disabled = true;
                return;
            }

            const filtered = dataArray.filter(item => item.parent_location_id == parentId);

            if (filtered.length > 0) {
                filtered.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.location_id;
                    option.textContent = item.location_name;
                    selectElement.appendChild(option);
                });
                selectElement.disabled = false;
            } else {
                selectElement.disabled = true;
            }
        }

        calculateFinalId() {
            if (!this.locationIdInput) return;

            const divId = this.divisionSelect ? this.divisionSelect.value : '';
            const secId = this.sectionSelect ? this.sectionSelect.value : '';
            const unitId = this.unitSelect ? this.unitSelect.value : '';

            this.locationIdInput.value = unitId || secId || divId;
        }

        async setLocation(locationId) {
            if (!locationId) return;

            try {
                const response = await fetch(`../ajax/get_location_path.php?id=${locationId}`);
                const path = await response.json();

                if (path.division_id) {
                    this.divisionSelect.value = path.division_id;
                    this.populateDropdown(this.sectionSelect, this.sectionsData, path.division_id, 'Select Section');
                    
                    const unitParent = path.section_id ? path.section_id : path.division_id;
                    this.populateDropdown(this.unitSelect, this.unitsData, unitParent, 'Select Unit');

                    if (path.section_id) this.sectionSelect.value = path.section_id;
                    if (path.unit_id) this.unitSelect.value = path.unit_id;
                    
                    this.calculateFinalId();
                }
            } catch (e) {
                console.error('Error setting location:', e);
            }
        }
        
        reset() {
            if(this.divisionSelect) this.divisionSelect.value = "";
            if(this.sectionSelect) {
                this.sectionSelect.innerHTML = '<option value="">Select Section</option>';
                this.sectionSelect.disabled = true;
            }
            if(this.unitSelect) {
                this.unitSelect.innerHTML = '<option value="">Select Unit</option>';
                this.unitSelect.disabled = true;
            }
            if(this.locationIdInput) this.locationIdInput.value = "";
        }
    }
    
    // Explicitly attach to window
    window.LocationManager = LocationManager;
}