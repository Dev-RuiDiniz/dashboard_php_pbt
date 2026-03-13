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

    function initFamilyFormMasks(form) {
        var cpfInput = form.querySelector('input[name="cpf_responsible"]');
        var rgInput = form.querySelector('input[name="rg_responsible"]');
        var phoneInput = form.querySelector('input[name="phone"]');

        if (!cpfInput && !rgInput && !phoneInput) {
            return;
        }

        if (cpfInput) {
            cpfInput.setAttribute('inputmode', 'numeric');
            cpfInput.setAttribute('maxlength', '14');
            bindMask(cpfInput, formatCpf);
        }

        if (rgInput) {
            rgInput.setAttribute('maxlength', '12');
            bindMask(rgInput, formatRg);
        }

        if (phoneInput) {
            phoneInput.setAttribute('inputmode', 'numeric');
            phoneInput.setAttribute('maxlength', '15');
            bindMask(phoneInput, formatPhone);
        }

        bindAgeCalculation(form);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form');
        forms.forEach(function (form) {
            initFamilyFormMasks(form);
        });
    });
})();
