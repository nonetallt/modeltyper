export interface FirstLevel {
  // columns
  id: number
  name: string
  // relations
  second_level_models: SecondLevel[]
}
export type FirstLevelEditable = Pick<FirstLevel, 'name'> & {
  second_level_models: SecondLevelEditable[]
}
