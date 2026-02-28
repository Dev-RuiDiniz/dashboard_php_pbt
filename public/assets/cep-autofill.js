(function () {
    'use strict';

    var CEP_DIGITS = 8;

    function onlyDigits(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function formatCep(value) {
        var digits = onlyDigits(value).slice(0, CEP_DIGITS);
        if (digits.length <= 5) {
            return digits;
        }

        return digits.slice(0, 5) + '-' + digits.slice(5);
    }

    function setFeedback(node, message, type) {
        if (!node) {
            return;
        }

        node.textContent = String(message || '');
        node.className = 'form-text';

        if (type === 'error') {
            node.classList.add('text-danger');
            return;
        }

        if (type === 'success') {
            node.classList.add('text-success');
            return;
        }

        node.classList.add('text-muted');
    }

    function setFieldValue(field, value, overwrite) {
        if (!field || typeof value !== 'string') {
            return;
        }

        if (value.trim() === '') {
            return;
        }

        if (!overwrite && String(field.value || '').trim() !== '') {
            return;
        }

        field.value = value;
    }

    function buildLookupUrl(cep) {
        return '/api/cep?cep=' + encodeURIComponent(cep);
    }

    async function lookupCep(cep) {
        var response = await fetch(buildLookupUrl(cep), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        var payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok || !payload || payload.ok !== true || typeof payload.data !== 'object') {
            var message = payload && typeof payload.message === 'string'
                ? payload.message
                : 'Falha ao consultar CEP.';
            throw new Error(message);
        }

        return payload.data;
    }

    function bindAddressForm(form) {
        var cepInput = form.querySelector('input[name="cep"]');
        var addressInput = form.querySelector('input[name="address"]');
        var neighborhoodInput = form.querySelector('input[name="neighborhood"]');
        var cityInput = form.querySelector('input[name="city"]');
        var stateInput = form.querySelector('input[name="state"]');
        var complementInput = form.querySelector('input[name="address_complement"]');
        var feedback = form.querySelector('[data-cep-feedback]');

        if (!cepInput || !addressInput || !neighborhoodInput || !cityInput || !stateInput) {
            return;
        }

        var lastLookupCep = '';
        var pending = false;

        cepInput.setAttribute('inputmode', 'numeric');
        cepInput.setAttribute('maxlength', '9');

        function onCepInput() {
            var formatted = formatCep(cepInput.value);
            cepInput.value = formatted;

            if (onlyDigits(formatted).length < CEP_DIGITS) {
                lastLookupCep = '';
                setFeedback(feedback, '', 'muted');
            }
        }

        async function triggerLookup() {
            var digits = onlyDigits(cepInput.value);

            if (digits.length !== CEP_DIGITS || pending || digits === lastLookupCep) {
                return;
            }

            pending = true;
            setFeedback(feedback, 'Buscando endereco pelo CEP...', 'muted');

            try {
                var data = await lookupCep(digits);
                var source = String(data.source || '').toUpperCase();

                setFieldValue(addressInput, String(data.address || ''), true);
                setFieldValue(neighborhoodInput, String(data.neighborhood || ''), true);
                setFieldValue(cityInput, String(data.city || ''), true);
                setFieldValue(stateInput, String(data.state || '').toUpperCase(), true);
                setFieldValue(complementInput, String(data.complement || ''), false);

                if (typeof data.cep === 'string' && data.cep.trim() !== '') {
                    cepInput.value = data.cep;
                } else {
                    cepInput.value = formatCep(digits);
                }

                lastLookupCep = digits;
                setFeedback(feedback, source !== '' ? 'Endereco preenchido via ' + source + '.' : 'Endereco preenchido.', 'success');
            } catch (error) {
                var message = error instanceof Error ? error.message : 'Nao foi possivel buscar o CEP.';
                setFeedback(feedback, message, 'error');
            } finally {
                pending = false;
            }
        }

        cepInput.addEventListener('input', onCepInput);
        cepInput.addEventListener('blur', function () {
            void triggerLookup();
        });
        cepInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                void triggerLookup();
            }
        });

        if (onlyDigits(cepInput.value).length === CEP_DIGITS) {
            void triggerLookup();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form');
        forms.forEach(function (form) {
            bindAddressForm(form);
        });
    });
})();
