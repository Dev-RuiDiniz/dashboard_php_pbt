(function () {
    'use strict';

    function onlyDigits(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function formatCpf(value) {
        var digits = onlyDigits(value).slice(0, 11);

        if (digits.length <= 3) {
            return digits;
        }
        if (digits.length <= 6) {
            return digits.slice(0, 3) + '.' + digits.slice(3);
        }
        if (digits.length <= 9) {
            return digits.slice(0, 3) + '.' + digits.slice(3, 6) + '.' + digits.slice(6);
        }

        return digits.slice(0, 3) + '.' + digits.slice(3, 6) + '.' + digits.slice(6, 9) + '-' + digits.slice(9);
    }

    function formatPhone(value) {
        var digits = onlyDigits(value).slice(0, 11);

        if (digits.length <= 2) {
            return digits;
        }

        if (digits.length <= 6) {
            return '(' + digits.slice(0, 2) + ') ' + digits.slice(2);
        }

        if (digits.length <= 10) {
            return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 6) + '-' + digits.slice(6);
        }

        return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 7) + '-' + digits.slice(7);
    }

    function formatRg(value) {
        var raw = String(value || '').toUpperCase().replace(/[^0-9X]/g, '').slice(0, 9);

        if (raw.length <= 2) {
            return raw;
        }
        if (raw.length <= 5) {
            return raw.slice(0, 2) + '.' + raw.slice(2);
        }
        if (raw.length <= 8) {
            return raw.slice(0, 2) + '.' + raw.slice(2, 5) + '.' + raw.slice(5);
        }

        return raw.slice(0, 2) + '.' + raw.slice(2, 5) + '.' + raw.slice(5, 8) + '-' + raw.slice(8);
    }

    function bindMask(input, formatter) {
        if (!input || typeof formatter !== 'function' || input.dataset.maskBound === '1') {
            return;
        }

        input.dataset.maskBound = '1';
        input.addEventListener('input', function () {
            input.value = formatter(input.value);
        });

        input.value = formatter(input.value);
    }

    function calculateAgeYears(dateValue) {
        var value = String(dateValue || '').trim();
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return '';
        }

        var birth = new Date(value + 'T00:00:00');
        if (Number.isNaN(birth.getTime())) {
            return '';
        }

        var today = new Date();
        var age = today.getFullYear() - birth.getFullYear();
        var monthDiff = today.getMonth() - birth.getMonth();
        var dayDiff = today.getDate() - birth.getDate();

        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age -= 1;
        }

        if (age < 0) {
            return '';
        }

        return String(age);
    }

    function initMasks(scope) {
        var root = scope || document;
        Array.prototype.slice.call(root.querySelectorAll('input[name="cpf_responsible"], input[name="cpf"]')).forEach(function (input) {
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('maxlength', '14');
            bindMask(input, formatCpf);
        });

        Array.prototype.slice.call(root.querySelectorAll('input[name="rg_responsible"], input[name="rg"]')).forEach(function (input) {
            input.setAttribute('maxlength', '12');
            bindMask(input, formatRg);
        });

        Array.prototype.slice.call(root.querySelectorAll('input[name="phone"], input[name$="[number]"], input[data-phone-number]')).forEach(function (input) {
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('maxlength', '15');
            bindMask(input, formatPhone);
        });

        Array.prototype.slice.call(root.querySelectorAll('input[name="income"], input[name="responsible_income"]')).forEach(function (input) {
            input.setAttribute('inputmode', 'decimal');
        });
    }

    function bindAgeCalculation(form) {
        var birthDateInput = form.querySelector('input[name="birth_date"]');
        var ageDisplayInput = form.querySelector('[data-family-age-display]');
        var hiddenAgeInput = form.querySelector('input[name="approx_age"]');

        if (!birthDateInput || !ageDisplayInput) {
            return;
        }

        function updateAge() {
            var age = calculateAgeYears(birthDateInput.value);
            ageDisplayInput.value = age !== '' ? age + ' anos' : '';
            if (hiddenAgeInput) {
                hiddenAgeInput.value = age;
            }
        }

        birthDateInput.addEventListener('input', updateAge);
        birthDateInput.addEventListener('change', updateAge);
        updateAge();
    }

    function bindMemberAdultRules(form) {
        var birthDateInput = form.querySelector('input[name="birth_date"]');
        var worksGroup = form.querySelector('[data-member-works-group]');
        var worksInput = worksGroup ? worksGroup.querySelector('input[name="works"]') : null;

        if (!birthDateInput || !worksGroup || !worksInput) {
            return;
        }

        function updateRules() {
            var age = calculateAgeYears(birthDateInput.value);
            var isAdult = age !== '' && Number(age) >= 18;
            worksGroup.classList.toggle('d-none', !isAdult);
            worksInput.disabled = !isAdult;
            if (!isAdult) {
                worksInput.checked = false;
            }
        }

        birthDateInput.addEventListener('input', updateRules);
        birthDateInput.addEventListener('change', updateRules);
        updateRules();
    }

    function initFormBehaviors(scope) {
        Array.prototype.slice.call((scope || document).querySelectorAll('form')).forEach(function (form) {
            bindAgeCalculation(form);
            bindMemberAdultRules(form);
        });
    }

    function initPhoneCollections(scope) {
        var root = scope || document;
        Array.prototype.slice.call(root.querySelectorAll('[data-phone-add][data-phone-target]')).forEach(function (addButton) {
            if (addButton.dataset.phoneCollectionBound === '1') {
                return;
            }

            var targetId = addButton.getAttribute('data-phone-target');
            var container = document.getElementById(targetId);
            var template = document.getElementById(targetId + '-template');
            if (!container || !template) {
                return;
            }

            addButton.dataset.phoneCollectionBound = '1';

            function sync() {
                var rows = Array.prototype.slice.call(container.querySelectorAll('[data-phone-row]'));
                rows.forEach(function (row, index) {
                    var number = row.querySelector('[name*="[number]"], [data-phone-number]');
                    var label = row.querySelector('[name*="[label]"], [data-phone-label]');
                    var radio = row.querySelector('[data-phone-primary]');
                    var hidden = row.querySelector('[data-phone-primary-hidden]');

                    if (number) {
                        number.name = 'phones[' + index + '][number]';
                    }
                    if (label) {
                        label.name = 'phones[' + index + '][label]';
                    }
                    if (radio) {
                        radio.name = 'phone_primary';
                        radio.value = String(index);
                    }
                    if (hidden) {
                        hidden.name = 'phones[' + index + '][is_primary]';
                    }
                });

                if (!rows.some(function (row) {
                    var radio = row.querySelector('[data-phone-primary]');
                    return radio && radio.checked;
                }) && rows[0]) {
                    var firstRadio = rows[0].querySelector('[data-phone-primary]');
                    if (firstRadio) {
                        firstRadio.checked = true;
                    }
                }

                rows.forEach(function (row) {
                    var hidden = row.querySelector('[data-phone-primary-hidden]');
                    var radio = row.querySelector('[data-phone-primary]');
                    if (hidden) {
                        hidden.value = radio && radio.checked ? '1' : '0';
                    }
                });
            }

            container.addEventListener('change', function (event) {
                if (event.target.matches('[data-phone-primary]')) {
                    sync();
                }
            });

            container.addEventListener('click', function (event) {
                var removeButton = event.target.closest('[data-phone-remove]');
                if (!removeButton) {
                    return;
                }

                var row = removeButton.closest('[data-phone-row]');
                if (row) {
                    row.remove();
                    sync();
                }
            });

            addButton.addEventListener('click', function () {
                container.appendChild(template.content.cloneNode(true));
                initMasks(container);
                sync();
            });

            sync();
        });
    }

    function initHousingFields(scope) {
        var root = scope || document;
        Array.prototype.slice.call(root.querySelectorAll('[data-housing-type]')).forEach(function (housingType) {
            if (housingType.dataset.housingFieldsBound === '1') {
                return;
            }

            var form = housingType.closest('form') || root;
            var rentGroup = form.querySelector('[data-rent-amount-group]');
            var otherGroup = form.querySelector('[data-housing-other-group]');
            if (!rentGroup && !otherGroup) {
                return;
            }

            var rentInput = rentGroup ? rentGroup.querySelector('input[name="rent_amount"]') : null;
            var otherInput = otherGroup ? otherGroup.querySelector('input[name="housing_type_other_details"]') : null;
            function sync() {
                var isRented = housingType.value === 'alugada';
                var isOther = housingType.value === 'outro';

                if (rentGroup) {
                    rentGroup.classList.toggle('d-none', !isRented);
                    if (!isRented && rentInput) {
                        rentInput.value = '';
                    }
                }
                if (otherGroup) {
                    otherGroup.classList.toggle('d-none', !isOther);
                    if (!isOther && otherInput) {
                        otherInput.value = '';
                    }
                }
            }

            housingType.dataset.housingFieldsBound = '1';
            housingType.addEventListener('change', sync);
            sync();
        });
    }

    function initChronicDiseaseOther(scope) {
        var root = scope || document;
        Array.prototype.slice.call(root.querySelectorAll('[data-chronic-other-group]')).forEach(function (group) {
            var form = group.closest('form') || root;
            var input = group.querySelector('input[name="chronic_disease_other_details"]');
            var options = Array.prototype.slice.call(form.querySelectorAll('[data-chronic-disease-option]'));
            if (group.dataset.chronicOtherBound === '1') {
                return;
            }

            function sync() {
                var otherChecked = options.some(function (checkbox) {
                    return checkbox.value === 'outra' && checkbox.checked;
                });
                group.classList.toggle('d-none', !otherChecked);
                if (!otherChecked && input) {
                    input.value = '';
                }
            }

            group.dataset.chronicOtherBound = '1';
            options.forEach(function (checkbox) {
                checkbox.addEventListener('change', sync);
            });
            sync();
        });
    }

    function initConditionalFields(scope) {
        var root = scope || document;
        Array.prototype.slice.call(root.querySelectorAll('[data-conditional-toggle]')).forEach(function (select) {
            if (select.dataset.conditionalFieldBound === '1') {
                return;
            }

            var target = select.getAttribute('data-conditional-toggle');
            var form = select.closest('form') || root;
            var group = Array.prototype.slice.call(form.querySelectorAll('[data-conditional-group]')).find(function (item) {
                return item.getAttribute('data-conditional-group') === target;
            });
            if (!group) {
                return;
            }

            var input = group.querySelector('input, textarea, select');
            function sync() {
                var visible = select.value === '1';
                group.classList.toggle('d-none', !visible);
                if (!visible && input) {
                    input.value = '';
                }
            }

            select.dataset.conditionalFieldBound = '1';
            select.addEventListener('change', sync);
            sync();
        });
    }

    function initFamilySelectorSearch(scope) {
        var root = scope || document;
        Array.prototype.slice.call(root.querySelectorAll('[data-family-selector-search]')).forEach(function (input) {
            var selector = input.getAttribute('data-family-selector-search');
            var select = selector ? root.querySelector(selector) : null;
            if (!select || input.dataset.familySearchBound === '1') {
                return;
            }

            input.dataset.familySearchBound = '1';
            var options = Array.prototype.slice.call(select.querySelectorAll('option'));

            function filterOptions() {
                var query = String(input.value || '').trim().toLocaleLowerCase();
                options.forEach(function (option) {
                    var text = String(option.textContent || '').trim().toLocaleLowerCase();
                    option.hidden = query !== '' && text.indexOf(query) === -1;
                });
            }

            input.addEventListener('input', filterOptions);
            filterOptions();
        });
    }

    function initFamilyPersonHub() {
        var hub = document.querySelector('[data-person-hub]');
        if (!hub) {
            return;
        }

        var toggleButton = hub.querySelector('[data-person-toggle]');
        var panel = hub.querySelector('[data-person-panel]');
        var typeButtons = Array.prototype.slice.call(hub.querySelectorAll('[data-person-type-btn]'));
        var sections = Array.prototype.slice.call(hub.querySelectorAll('[data-person-section]'));
        var memberTypeInput = hub.querySelector('[data-member-person-type]');
        var memberTitle = hub.querySelector('[data-member-form-title]');
        var memberSubmitLabel = hub.querySelector('[data-member-submit-label]');
        var memberSection = hub.querySelector('[data-person-section="member"]');
        var isMemberEditMode = memberSection && memberSection.getAttribute('data-member-edit-mode') === '1';
        if (!toggleButton || !panel || typeButtons.length === 0 || sections.length === 0) {
            return;
        }

        function setPanelOpen(open) {
            panel.classList.toggle('d-none', !open);
            panel.setAttribute('data-person-open', open ? '1' : '0');
            toggleButton.textContent = open ? 'Fechar cadastro' : 'Adicionar pessoa';
        }

        function focusFirstInput(section) {
            if (!section) {
                return;
            }

            var target = section.querySelector('input:not([type="hidden"]):not([readonly]), select:not([disabled]), textarea');
            if (target && typeof target.focus === 'function') {
                target.focus();
            }
        }

        function updateMemberLabels(personType) {
            if (!memberTitle || !memberSubmitLabel || personType === 'principal' || personType === 'child') {
                return;
            }

            memberTitle.textContent = isMemberEditMode ? 'Editar membro familiar' : 'Adicionar membro familiar';
            memberSubmitLabel.textContent = isMemberEditMode ? 'Salvar membro' : 'Adicionar membro';
        }

        function setPersonType(type) {
            var personType = type === 'principal' || type === 'child' ? type : 'member';

            typeButtons.forEach(function (button) {
                var active = button.getAttribute('data-person-type-btn') === personType;
                button.classList.toggle('btn-teal', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('btn-outline-secondary', !active);
            });

            sections.forEach(function (section) {
                section.classList.toggle('d-none', section.getAttribute('data-person-section') !== personType);
            });

            if (memberTypeInput) {
                memberTypeInput.value = 'member';
            }

            updateMemberLabels(personType);
            setPanelOpen(true);
            focusFirstInput(hub.querySelector('[data-person-section="' + personType + '"]'));
        }

        toggleButton.addEventListener('click', function () {
            setPanelOpen(panel.getAttribute('data-person-open') !== '1');
        });

        typeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setPersonType(button.getAttribute('data-person-type-btn') || 'member');
            });
        });

        var activeButton = typeButtons.find(function (button) {
            return button.classList.contains('btn-teal');
        });
        setPersonType(activeButton ? activeButton.getAttribute('data-person-type-btn') : 'member');
        if (panel.getAttribute('data-person-open') !== '1') {
            setPanelOpen(false);
        }
    }

    window.DashboardFormEnhancements = {
        formatCpf: formatCpf,
        formatPhone: formatPhone,
        formatRg: formatRg,
        initMasks: initMasks,
        initFormBehaviors: initFormBehaviors,
        initFamilySelectorSearch: initFamilySelectorSearch
    };

    document.addEventListener('DOMContentLoaded', function () {
        initMasks(document);
        initFormBehaviors(document);
        initPhoneCollections(document);
        initHousingFields(document);
        initChronicDiseaseOther(document);
        initConditionalFields(document);
        initFamilySelectorSearch(document);
        initFamilyPersonHub();
    });
})();
