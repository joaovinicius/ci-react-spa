import {ActionType, PlopGeneratorConfig} from "plop";

const generateMigrationTimestamp = (): string => {
  return new Date().toISOString()
    .replace('T', '-')
    .split('.')[0]
    .replace(/:/g, '');
};

export const codeigniterMigration: PlopGeneratorConfig = {
  description: "Create a new Codeigniter migration",
  prompts: [
    {
      type: "input",
      name: "name",
      message: "Migration name:",
    },
    {
      type: "input",
      name: "table",
      message: "Table name:",
    },
  ],
  actions: (data) => {
    const actions: ActionType[] = [];
    const timestamp = generateMigrationTimestamp()
    actions.push({
      type: "add",
      path: `./src/backend/Database/Migrations/${timestamp}_{{pascalCase name}}.php`,
      templateFile: "./templates/codeigniter/migration.php.hbs",
    });

    return actions;
  },
};

const defaultPrompts = [
  {
    type: "input",
    name: "name",
    message: "Entity name:",
  },
  {
    type: "input",
    name: "table",
    message: "Table name:",
  },
]

const controllerAction = {
  type: "add",
  path: `./src/backend/Controllers/Api/{{pascalCase name}}Controller.php`,
  templateFile: "./templates/codeigniter/controller.php.hbs",
}

const serviceAction = {
  type: "add",
  path: `./src/backend/Services/{{pascalCase name}}Service.php`,
  templateFile: "./templates/codeigniter/service.php.hbs",
}

const repositoryAction = {
  type: "add",
  path: `./src/backend/Repositories/{{pascalCase name}}Repository.php`,
  templateFile: "./templates/codeigniter/repository.php.hbs",
}

const modelAction = {
  type: "add",
  path: `./src/backend/Models/{{pascalCase name}}Model.php`,
  templateFile: "./templates/codeigniter/model.php.hbs",
}

const entityAction = {
  type: "add",
  path: `./src/backend/Entities/{{pascalCase name}}.php`,
  templateFile: "./templates/codeigniter/entity.php.hbs",
}

export const codeigniterController: PlopGeneratorConfig = {
  description: "Create a new Codeigniter Controller",
  prompts: defaultPrompts,
  actions: [controllerAction],
};

export const codeigniterService: PlopGeneratorConfig = {
  description: "Create a new Codeigniter Service",
  prompts: defaultPrompts,
  actions: [serviceAction],
};

export const codeigniterRepository: PlopGeneratorConfig = {
  description: "Create a new Codeigniter Repository",
  prompts: defaultPrompts,
  actions: [repositoryAction],
};

export const codeigniterModel: PlopGeneratorConfig = {
  description: "Create a new Codeigniter Model",
  prompts: defaultPrompts,
  actions: [modelAction],
};

export const codeigniterEntity: PlopGeneratorConfig = {
  description: "Create a new Codeigniter Entity",
  prompts: defaultPrompts,
  actions: [entityAction],
};


export const migrationAction = {
  type: "add",
  path: `./src/backend/Database/Migrations/${generateMigrationTimestamp()}_Create{{pascalCase table}}Table.php`,
  templateFile: "./templates/codeigniter/crud.migration.php.hbs",
}

export const codeigniterCrud: PlopGeneratorConfig = {
  description: "Create a new Codeigniter CRUD",
  prompts: defaultPrompts,
  actions: [
    migrationAction,
    controllerAction,
    serviceAction,
    repositoryAction,
    modelAction,
    entityAction,
  ],
};
