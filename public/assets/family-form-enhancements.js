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
        if (!input || typeof formatter !== 'function') {
            return;
        }

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

    function bindAgeCalculation(form) {
        var birthDateInput = form.querySelector('input[name="birth_date"]');
        var ageDisplayInput = form.querySelector('[data-family-age-display]');

        if (!birthDateInput || !ageDisplayInput) {
            return;
        }

        function updateAge() {
            var age = calculateAgeYears(birthDateInput.value);
            ageDisplayInput.value = age !== '' ? age + ' anos' : '';
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

    function initFamilyFormMasks(form) {
        var cpfInputs = Array.prototype.slice.call(form.querySelectorAll('input[name="cpf_responsible"], input[name="cpf"]'));
        var rgInputs = Array.prototype.slice.call(form.querySelectorAll('input[name="rg_responsible"], input[name="rg"]'));
        var phoneInputs = Array.prototype.slice.call(form.querySelectorAll('input[name="phone"]'));
        var moneyInputs = Array.prototype.slice.call(form.querySelectorAll('input[name="income"], input[name="responsible_income"]'));

        cpfInputs.forEach(function (cpfInput) {
            cpfInput.setAttribute('inputmode', 'numeric');
            cpfInput.setAttribute('maxlength', '14');
            bindMask(cpfInput, formatCpf);
        });

        rgInputs.forEach(function (rgInput) {
            rgInput.setAttribute('maxlength', '12');
            bindMask(rgInput, formatRg);
        });

        phoneInputs.forEach(function (phoneInput) {
            phoneInput.setAttribute('inputmode', 'numeric');
            phoneInput.setAttribute('maxlength', '15');
            bindMask(phoneInput, formatPhone);
        });

        moneyInputs.forEach(function (moneyInput) {
            moneyInput.setAttribute('inputmode', 'decimal');
        });

        bindAgeCalculation(form);
        bindMemberAdultRules(form);
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
        var relationshipGroup = hub.querySelector('[data-member-relationship-group]');
        var relationshipSelect = relationshipGroup ? relationshipGroup.querySelector('select[name="relationship"]') : null;
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
            if (!memberTitle || !memberSubmitLabel) {
                return;
            }

            if (personType === 'principal' || personType === 'child') {
                return;
            }

            memberTitle.textContent = isMemberEditMode ? 'Editar membro familiar' : 'Adicionar membro familiar';
            memberSubmitLabel.textContent = isMemberEditMode ? 'Salvar membro' : 'Adicionar membro';
            if (relationshipGroup) {
                relationshipGroup.classList.remove('d-none');
            }
            if (relationshipSelect) {
                relationshipSelect.disabled = false;
            }
        }

        function setPersonType(type) {
            var personType = 'member';
            if (type === 'principal' || type === 'child') {
                personType = type;
            }

            typeButtons.forEach(function (button) {
                var active = button.getAttribute('data-person-type-btn') === personType;
                button.classList.toggle('btn-teal', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('btn-outline-secondary', !active);
            });

            sections.forEach(function (section) {
                var key = section.getAttribute('data-person-section');
                var visible = false;
                if (personType === 'principal') {
                    visible = key === 'principal';
                } else if (personType === 'child') {
                    visible = key === 'child';
                } else {
                    visible = key === 'member';
                }
                section.classList.toggle('d-none', !visible);
            });

            if (memberTypeInput) {
                memberTypeInput.value = 'member';
            }

            updateMemberLabels(personType);
            setPanelOpen(true);
            focusFirstInput(hub.querySelector('[data-person-section="' + (personType === 'principal' ? 'principal' : (personType === 'child' ? 'child' : 'member')) + '"]'));
        }

        toggleButton.addEventListener('click', function () {
            var isOpen = panel.getAttribute('data-person-open') === '1';
            setPanelOpen(!isOpen);
        });

        typeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setPersonType(button.getAttribute('data-person-type-btn') || 'member');
            });
        });

        var activeButton = typeButtons.find(function (button) {
            return button.classList.contains('btn-teal');
        });
        var initialType = activeButton ? activeButton.getAttribute('data-person-type-btn') : 'member';
        setPersonType(initialType || 'member');
        if (panel.getAttribute('data-person-open') !== '1') {
            setPanelOpen(false);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form');
        forms.forEach(function (form) {
            initFamilyFormMasks(form);
        });
        initFamilyPersonHub();
    });
})();
