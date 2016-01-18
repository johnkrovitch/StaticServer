server 'ks3313894.kimsufi.com',
    user: 'johnkrovitch',
    roles: %w{web app db},
    ssh_options: {
        user: 'johnkrovitch',
        keys: %w(/home/johnkrovitch/.ssh/id_rsa),
        forward_agent: false,
        auth_methods: %w(publickey password)
}
