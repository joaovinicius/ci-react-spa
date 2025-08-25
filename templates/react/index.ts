import { ActionType, PlopGeneratorConfig } from "plop";

export const reactComponent: PlopGeneratorConfig = {
  description: "Create a new React component",
  prompts: [
    {
      type: "input",
      name: "name",
      message: "Component name:",
    },
    {
      type: "list",
      name: "type",
      message: "Type:",
      choices: ["components", "views"],
      default: "component",
    },
    {
      type: "input",
      name: "folder",
      message: "Component folder:",
      default: "",
    },
  ],
  actions: (data) => {
    const actions: ActionType[] = [];
    const path = `./src/frontend/${data?.type}`;
    const splitedFolders = data?.folder?.split("/");
    const folders = splitedFolders.filter((folder) => !!folder).map(item => item.trim());
    const fullPath = folders.length > 0 ? `${path}/${folders.join("/")}` : path;

    actions.push({
      type: "add",
      path: `${fullPath}/{{pascalCase name}}.tsx`,
      templateFile: "./templates/react/component.tsx.hbs",
    });

    actions.push({
      type: "add",
      path: `${fullPath}/{{pascalCase name}}.test.tsx`,
      templateFile: "./templates/react/component.test.tsx.hbs",
    });

    if (data?.context === "shared") {
      actions.push({
        type: "add",
        path: `${fullPath}/{{pascalCase name}}.stories.tsx`,
        templateFile: "./templates/react/component.stories.tsx.hbs",
      });
    }

    return actions;
  },
};
