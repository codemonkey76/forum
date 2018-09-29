<template>
    <div :id="'reply-'+id" class="card my-3" :class="isBest?'border-success':''">
        <div class="card-header" :class="isBest?'panel-success':''">
            <div class="level">
                <h5 class="flex">
                    <a :href="'/profile/'+data.owner.name" v-text="data.owner.name"></a>
                    said <span v-text="ago"></span>...
                </h5>

                <div>
                    <favorite v-if="signedIn" :reply="data"></favorite>
                </div>

            </div>
        </div>
        <div class="card-body">
            <div v-if="editing">
                <form @submit="update">
                    <div class="form-group">
                        <textarea class="form-control" v-model="body" required></textarea>
                    </div>
                    <button class="btn btn-sm btn-primary">Update</button>
                    <button class="btn btn-sm btn-link" @click="editing = false" type="button">Cancel</button>
                </form>
            </div>
            <div v-else v-html="body"></div>
        </div>
        <div class="card-footer level">
            <div v-if="authorize('updateReply', reply)">
                <button class="btn btn-sm mr-1" @click="editing = true">Edit</button>
                <button class="btn btn-danger btn-sm" @click="destroy">Delete</button>
            </div>
            <button class="btn btn-default btn-sm ml-auto" @click="markBestReply" v-show="! isBest">Best Reply?</button>
        </div>
    </div>
</template>

<script>
    import Favorite from './Favorite';
    import moment from 'moment';

    export default {
        props: ['data'],

        components: {Favorite},
        data() {
            return {
                id: this.data.id,
                editing: false,
                body: this.data.body,
                isBest: this.data.isBest,
                reply: this.data,
            }
        },

        computed: {
            ago() {
                return moment(this.data.created_at + "Z").fromNow();
            },
        },
        created() {
            window.events.$on('best-reply-selected', id => {
               this.isBest = (id === this.id)
            });
        },
        methods: {
            markBestReply() {
                axios.post('/replies/' + this.id + '/best');
                window.events.$emit('best-reply-selected', this.id);
            },
            update() {
                axios.patch('/replies/' + this.id, {
                    body: this.body
                })
                    .catch(error => {
                        flash(error.response.data, 'danger');
                    });

                this.editing = false;

                flash('Updated!');
            },

            destroy() {
                axios.delete('/replies/' + this.id);
                this.$emit('deleted', this.id);
            }
        }
    }
</script>