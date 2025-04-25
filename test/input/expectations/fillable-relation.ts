export type FirstLevelEditable = Pick<FirstLevel, 'name'> & {
  second_level_models: SecondLevelEditable[]
}
