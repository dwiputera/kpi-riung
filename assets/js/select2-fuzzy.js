(function (global) {
    function levenshtein(a, b) {
        if (a.length === 0) return b.length;
        if (b.length === 0) return a.length;
        const m = Array.from({ length: b.length + 1 }, (_, i) => [i]);
        for (let j = 0; j <= a.length; j++) m[0][j] = j;
        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                m[i][j] = b[i - 1] === a[j - 1]
                    ? m[i - 1][j - 1]
                    : Math.min(m[i - 1][j - 1] + 1, m[i][j - 1] + 1, m[i - 1][j] + 1);
            }
        }
        return m[b.length][a.length];
    }

    function fuzzyMatcher(params, data) {
        if (!params.term || !data.text) return data;
        const terms = params.term.toLowerCase().split(/\s+/);
        const words = data.text.toLowerCase().split(/\s+/);
        return terms.every(t => words.some(w => w.includes(t) || levenshtein(w, t) <= 1)) ? data : null;
    }

    function applyFuzzySelect2(selector = '.select2') {
        $(selector).select2({
            width: '100%',
            dropdownAutoWidth: true,
            placeholder: 'Filter...',
            allowClear: true,
            matcher: fuzzyMatcher
        });
    }

    // Export globally
    global.FuzzySelect2 = {
        apply: applyFuzzySelect2,
        matcher: fuzzyMatcher
    };

})(window);
