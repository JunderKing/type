var app = new Vue({
    el: '#app',
    data: {
        userId: 1,
        phraseId: 1,
        phraseArr: [
            {id: 1, phrase: 'Where there is will there is a way!'},
            {id: 2, phrase: 'mysql -uroot -p'},
        ],
        blinkShow: false,
        curIndex: 0,
        errIndexArr: [],
        timerId: '',
        isCorrect: 1,
        newItem: {
            phrase: '',
            desc: '',
            firstClass: '默认',
            secondClass: '默认',
            thirdClass: '默认',
            level: 0,
        }
    },

    methods: {
        start: function(){
            if (!this.timerId) {
                this.blinkShow = true;
                var that = this;
                this.timerId = setInterval(function(){
                    that.blinkShow = that.blinkShow ? false : true;
                }, 500);
            }
        },

        finish: function(){
            if (this.timerId) {
                clearInterval(this.timerId);
            }
            this.blinkShow = false;
            this.timerId = '';
        },

        addPhrase: function(){
            var formData = new FormData();
            formData.append('userId', this.userId);
            formData.append('phrase', this.newItem.phrase);
            formData.append('desc', this.newItem.desc);
            formData.append('firstClass', this.newItem.firstClass);
            formData.append('secondClass', this.newItem.secondClass);
            formData.append('thirdClass', this.newItem.thirdClass);
            formData.append('level', this.newItem.level);
            this.$http.post('/type/index.php?action=addPhrase', formData).then(res => {
                var resJson = res.body;
                console.log(resJson);
            }, err => {
                console.log('error');
            })
        },

        check: function(event){
            if(this.curIndex == 0) {
                this.startTime = Date.parse(new Date());
            }
            //无视控制键
            if((event.keyCode >=48&&event.keyCode<=90) ||
                event.keyCode==32 ||
                event.keyCode==106 ||
                event.keyCode==107 ||
                (event.keyCode>=109&&event.keyCode<=111) ||
                (event.keyCode>=186&&event.keyCode<=192) ||
                (event.keyCode>=219&&event.keyCode<=222))
            {} else { return; }
            //移动光标，标记错误
            if(event.key == this.phraseArr[0].phrase.split('')[this.curIndex]){
                this.curIndex++;
            } else {
                this.isCorrect = 0;
                this.errIndexArr.push(this.curIndex);
                this.curIndex++;
            }
            //使光标重新闪动
            this.blinkShow = true;
            clearInterval(this.timerId);
            var that = this;
            this.timerId = setInterval(function(){
                that.blinkShow = that.blinkShow ? false : true;
            }, 500);
            //如果是最后一个字母则更换词组
            if(this.curIndex >= this.phraseArr[0].phrase.length) {
                this.curIndex = 0;
                this.errIndexArr = [];
                var curPhrase = this.phraseArr.shift();
                var formData = new FormData();
                formData.append('userId', this.userId);
                formData.append('phraseId', curPhrase.id);
                formData.append('isCorrect', this.isCorrect);
                formData.append('spendTime', Date.parse(new Date())- this.startTime);
                var that = this;
                this.$http.post('/type/index.php?action=updPhrase', formData).then(res => {
                    var resObj = res.body;
                    if (resObj.errno == 0) {
                        that.phraseArr.push(resObj.data);
                    }
                    console.log(resObj);
                }, err => {
                    console.log('error');
                });
            }
        }
    }
})
