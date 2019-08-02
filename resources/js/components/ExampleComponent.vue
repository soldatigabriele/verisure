<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Example Component</div>

                <div class="card-body">
                    <div v-for="model in show">
                        {{model.name}}
                        {{model.country}} | {{model.age}} [{{model.present ? 'yes' : 'no'}}]
                        
                        <!-- <input v-model="model.name">  -->
                        <input type="checkbox" v-model="model.present"> 
                    </div>
                    total age: {{total}}
                    page: {{pagination.currentpage}}
                    <button @click="pagination.currentpage++">+</button>
                    <button @click="decrementpage()">-</button>
                    <button @click="incrementpage()">+</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash'
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
            return {
                show: false,
                models: {
                    p1: [
                        {
                            name: 'gab',
                            age: 28,
                            present: false,
                            country: 'italy'
                        },
                        {
                            name: 'dean',
                            age: 35,
                            present: false,
                            country: 'england'
                        }
                    ],
                    p2: [
                        {
                            name: 'bob',
                            age: 52,
                            present: false,
                            country: 'italy'
                        },
                        {
                            name: 'harry',
                            age: 99,
                            present: false,
                            country: 'england'
                        }
                    ]
                },
                pagination: {
                    currentpage: 1
                }
            }
        },
        methods: {
            incrementpage(){
                this.pagination.currentpage++
            },
            decrementpage(){
                this.pagination.currentpage--
            }
        },
        mounted () {
            this.show = this.models.p1
        },
        computed: {
            total(){
                var total = 0
                _.each(this.show , (item) => {
                    total = total + item.age
                })
                return total
            }
        },
        watch: {
            models: {
                deep: true,
                handler(newval,oldval){
                    console.log(newval, oldval)
                    // axios.get('/api/people?page=' + pagination.currentpage)
                }
            },
            'pagination.currentpage': {
                handler(newval, oldval) {
                    if(this.pagination.currentpage == 1){
                        this.show = this.models.p1
                    }
                    if(this.pagination.currentpage == 2){
                        this.show = this.models.p2
                    }
                }
            }
        }
    }
</script>
