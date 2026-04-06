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
        initFormBehaviors: initFormBehaviors
    };

    document.addEventListener('DOMContentLoaded', function () {
        initMasks(document);
        initFormBehaviors(document);
        initFamilyPersonHub();
    });
})();
