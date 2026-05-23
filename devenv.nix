{ ... }:

{
  languages = {
    php = {
      enable = true;
      extensions = [
        "pdo_pgsql"
        "pgsql"
        "mbstring"
      ];
    };
  };

  services.postgres = {
    enable = true;
    initialDatabases = [ { name = "my_app_db"; } ];
    listen_addresses = "127.0.0.1";
    port = 5432;
  };
}
