<template>
  <div class="container">
    <div class="row mt-5" v-if="$gate.isAdminOrGerente()">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Lista de Usuários</h3>

            <div class="card-tools">
              <button class="btn btn-success" @click="newModal">
                Adicionar Novo Usuário
                <i class="fas fa-user-plus fa-fw"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>CPF</th>
                  <th>Email</th>
                  <th>Tipo</th>
                  <th>Data Criação</th>
                  <th>Data Atualização</th>
                  <th>Modificar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users.data" :key="user.id">
                  <td>{{ user.name | upText}}</td>
                  <td>{{ user.cpf | VMask('###.###.###-##') }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.tipo | upText}}</td>
                  <td>{{ user.created_at | formatData}}</td>
                  <td>{{ user.updated_at | formatData}}</td>
                  <td>
                    <a href="#" @click="showUser(user.id)">
                      <i class="fa fa-eye cyan"></i>
                    </a>
                    <a href="#" @click="editModal(user)">
                      <i class="fa fa-edit blue"></i>
                    </a>
                    <a href="#" @click="deleteUser(user.id)">
                      <i class="fa fa-trash red"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <pagination :data="users" @pagination-change-page="getResults" :limit="1">
              <span slot="prev-nav">&lt; Anterior</span>
              <span slot="next-nav">Próximo &gt;</span>
            </pagination>
          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>

    <!-- Modal -->
    <div
      class="modal fade"
      id="novoUsuario"
      tabindex="-1"
      role="dialog"
      aria-labelledby="novoUsuarioLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 v-if="editMode" class="modal-title" id="novoUsuarioLabel">Atualizar Usuário</h5>
            <h5 v-else class="modal-title" id="novoUsuarioLabel">Adicionar Novo Usuário</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form @submit.prevent="editMode ? updateUser(): createUser()">
            <div class="modal-body">
              <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" class="form-control" name="name" id="name" v-model="fields.name" />
                <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
              </div>

              <div class="form-group">
                <label for="cpf">CPF</label>
                <input
                  type="text"
                  class="form-control"
                  name="cpf"
                  id="cpf"
                  maxlength="11"
                  v-model="fields.cpf"
                />
                <div v-if="errors && errors.cpf" class="text-danger">{{ errors.cpf[0] }}</div>
              </div>

              <div class="form-group">
                <label for="email">E-mail</label>
                <input
                  type="email"
                  class="form-control"
                  name="email"
                  id="email"
                  v-model="fields.email"
                />
                <div v-if="errors && errors.email" class="text-danger">{{ errors.email[0] }}</div>
              </div>

              <div class="form-group">
                <label for="tipo">Tipo</label>
                <select class="custom-select" id="tipo" name="tipo" v-model="fields.tipo">
                  <option value selected>Selecione...</option>
                  <option value="admin">Administrador</option>
                  <option value="default">Padrão</option>
                  <option value="gerente">Contrato</option>
                </select>
                <div v-if="errors && errors.tipo" class="text-danger">{{ errors.tipo[0] }}</div>
              </div>

              <div class="form-group">
                <label for="password">Senha</label>
                <input
                  type="password"
                  class="form-control"
                  name="password"
                  autocomplete="on"
                  id="password"
                  v-model="fields.password"
                />
                <div v-if="errors && errors.password" class="text-danger">{{ errors.password[0] }}</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">
                Cancelar
                <i class="fas fa-times"></i>
              </button>
              <button v-if="editMode" type="submit" class="btn btn-success">
                Atualizar Usuário
                <i class="fas fa-user-edit"></i>
              </button>
              <button v-else type="submit" class="btn btn-success">
                Criar Usuário
                <i class="fas fa-user-plus"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-if="!$gate.isAdminOrGerente()">
      <not-found></not-found>
    </div>
  </div>
</template>

<script>
export default {
  created() {
    Fire.$on("searching", () => {
      let query = this.$parent.search;
      axios
        .get("api/findUser?query=" + query)
        .then(data => {
          console.log(data);
          this.users = data.data;
        })
        .catch(() => {});
    });
    this.$Progress.start();
    this.carregarUsuarios();
  },
  mounted() {
    // Fetch initial results
    //	this.getResults();
  },

  data() {
    return {
      editMode: false,
      fields: [],
      users: {},
      errors: {},
      success: false,
      loaded: true,
      action: "/api/user"
    };
  },
  methods: {
    getResults(page = 1) {
      axios.get("api/user?page=" + page).then(response => {
        this.users = response.data;
      });
    },
    editModal(user) {
      this.loaded = true;
      this.errors = {};
      this.success = false;
      this.fields = {}; //Clear input fields.
      this.editMode = true;
      this.fields = { ...user };

      $("#novoUsuario").modal("show");
    },
    newModal() {
      this.editMode = false;
      this.fields = {}; //Clear input fields.
      this.loaded = true;
      this.success = true;
      this.errors = {};
      $("#novoUsuario").modal("show");
    },
    deleteUser(id) {
      swal
        .fire({
          title: "você tem certeza?",
          text: "Está ação não poderá ser revertida!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sim, deletar!",
          cancelButtonText: "Cancelar!"
        })
        .then(result => {
          if (result.value) {
            //send request to the server
            axios
              .delete("api/user/" + id)
              .then(response => {
                swal.fire(
                  "Deletado!",
                  "Usuário deletado com sucesso!",
                  "success"
                );
                this.carregarUsuarios();
              })
              .catch(error => {
                swal.fire("Falha!", "Problema ao deletar o usuário", "error");
              });
          }
        });
    },
    carregarUsuarios() {
      if (this.$gate.isAdminOrGerente()) {
        axios
          .get("api/user")
          .then(({ data }) => (this.users = data), this.$Progress.finish())
          .catch(error => {
            Toast.fire({
              icon: "error",
              title: "Falha Ao Carregar Lista De Usuários!"
            }),
              this.$Progress.fail();
          });
      }
    },

    createUser() {
      if (this.loaded) {
        this.$Progress.start();

        this.loaded = false;
        this.success = false;
        this.errors = {};
        axios
          .post(this.action, this.fields)
          .then(response => {
            this.fields = {}; //Clear input fields.
            this.loaded = true;
            this.success = true;
            $("#novoUsuario").modal("hide");
            Toast.fire({
              icon: "success",
              title: "Usuário Atualizado com Sucesso !"
            });

            this.$Progress.finish();
            this.carregarUsuarios();
          })
          .catch(error => {
            this.loaded = true;
            if (error.response.status === 422) {
              this.errors = error.response.data.errors || {};
            }
            Toast.fire({
              icon: "error",
              title: "Ops Houve Um Problema No Formulário, Tente Novamente!"
            });

            this.$Progress.fail();
          });
      }
    },
    updateUser() {
      if (this.loaded) {
        this.$Progress.start();

        this.loaded = false;
        this.success = false;
        this.errors = {};
        axios
          .put("api/user/" + this.fields.id, this.fields)
          .then(response => {
            this.fields = {}; //Clear input fields.
            this.loaded = true;
            this.success = true;
            $("#novoUsuario").modal("hide");
            Toast.fire({
              icon: "success",
              title: "Usuário Atualizado com Sucesso !"
            });

            this.$Progress.finish();
            this.carregarUsuarios();
          })
          .catch(error => {
            this.loaded = true;
            this.success = false;
            if (error.response.status === 422) {
              this.errors = error.response.data.errors || {};
            }
            Toast.fire({
              icon: "error",
              title: "Ops Houve Um Problema No Formulário, Tente Novamente!"
            });

            this.$Progress.fail();
          });
      }
    },

    showUser(id) {
      if (this.loaded) {
        this.$Progress.start();

        this.loaded = false;
        this.success = false;
        this.errors = {};
        axios
          .get("api/user/" + id)
          .then(response => {
            this.fields = {}; //Clear input fields.
            this.loaded = true;
            this.success = true;
            Toast.fire({
              icon: "warning",
              title: "Método Show em desenvolvimento  !"
            });

            this.$Progress.finish();
          })
          .catch(error => {
            this.loaded = true;
            this.success = false;

            Toast.fire({
              icon: "error",
              title: "Ops Houve Um Problema, Tente Novamente!"
            });

            this.$Progress.fail();
          });
      }
    }
  }
};
</script>
