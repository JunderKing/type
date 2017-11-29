var app = new Vue({
    el: '#app',

    data: {
        codeString: "",
        codeList: [
            "class Index{",
            "    public function __construct(){",
            "        echo 'Hello world!'",
            "    }",
            "}",
        ],
        cursorX: 0,
        cursorY: 0,
    },

    methods: {
    }
})
